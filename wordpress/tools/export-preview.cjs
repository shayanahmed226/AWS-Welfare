// Export the actual rendered WordPress theme for client review on static hosting.
// This is a design preview; PHP, administration and financial workflows stay in WordPress.
const { chromium } = require('playwright');
const fs = require('fs'); const path = require('path');
const source = process.env.AWSW_PREVIEW_SOURCE || 'http://localhost:8090';
const out = process.env.AWSW_PREVIEW_OUT || '/tmp/awsw-client-preview';
const prefix = '/AWS-Welfare/';
const pages = ['', 'about','team','programs','food','education','water','medical','campaigns','donate','donation-status','impact','updates','transparency','get-involved','volunteer','volunteer-application','request-assistance','partnerships','in-kind','blog','gallery','faq','contact','privacy','donation-policy','terms','donation-confirmation','submission-confirmation','payment-cancelled'];

(async () => {
 fs.mkdirSync(out, {recursive:true});
 fs.cpSync(path.resolve(__dirname,'../aws-welfare-theme/assets'),path.join(out,'_preview/theme/assets'),{recursive:true});
 fs.cpSync(path.resolve(__dirname,'../aws-welfare-core/assets'),path.join(out,'_preview/core/assets'),{recursive:true});
 const b = await chromium.launch({executablePath:'/usr/bin/chromium',args:['--no-sandbox']}); const c = await b.newContext({javaScriptEnabled:false});const p=await c.newPage();
 const postResponse=await c.request.get(source+'/wp-json/wp/v2/posts?per_page=100&_fields=slug');if(!postResponse.ok())throw Error('Cannot read public editorial routes');if(Number(postResponse.headers()['x-wp-total'])>100)throw Error('More than 100 posts; expand exporter pagination before publishing.');for(const post of await postResponse.json()){if(post.slug!=='hello-world'&&!pages.includes(post.slug))pages.push(post.slug);}
 for (const lang of ['en','ur']) for (const slug of pages) {
  const response = await p.goto(`${source}/${slug ? slug+'/' : ''}?lang=${lang}${slug==='donate'?'&design_preview=1':''}`);
  if (response.status() !== 200) throw Error(`Export failed: ${slug} ${lang}`);
  await p.evaluate(({prefix,source,lang}) => {
   const local = new URL(source);
   const remap = value => {
    if (!value || value.startsWith('#') || value.startsWith('mailto:') || value.startsWith('tel:')) return value;
    const u = new URL(value,location.href);if (u.origin !== local.origin) return value;
    if (u.pathname.includes('/wp-content/themes/aws-welfare-theme/assets/')) return prefix+'_preview/theme/assets/'+u.pathname.split('/assets/')[1];
    if (u.pathname.includes('/wp-content/plugins/aws-welfare-core/assets/')) return prefix+'_preview/core/assets/'+u.pathname.split('/assets/')[1];
    if (u.pathname.startsWith('/wp-') || u.pathname.endsWith('.php')) return '#';
    const targetLang=u.searchParams.get('lang') || lang;
    return prefix+(targetLang==='ur'?'ur/':'')+u.pathname.replace(/^\//,'')+u.hash;
   };
   document.querySelectorAll('link').forEach(el => {if(el.rel==='stylesheet'){el.href=remap(el.href);}else if(['canonical','alternate'].includes(el.rel)&&!el.type){el.href='https://shayanahmed226.github.io'+remap(el.href);}else{el.remove();}});
   document.querySelectorAll('script').forEach(el=>{if(el.src && /aws-welfare-(theme|core)\/assets\//.test(el.src)){el.src=remap(el.src);}else{el.remove();}});
   document.querySelectorAll('a[href]').forEach(el=>el.setAttribute('href',remap(el.getAttribute('href'))));
   document.querySelectorAll('img[src]').forEach(el=>el.setAttribute('src',remap(el.getAttribute('src'))));
   document.querySelectorAll('form').forEach(form=>{form.setAttribute('action','#');form.removeAttribute('method');form.setAttribute('data-client-preview','');form.querySelectorAll('[name]').forEach(el=>el.removeAttribute('name'));form.querySelectorAll('input[type=hidden]').forEach(el=>el.remove());form.querySelectorAll('button[type=submit],button:not([type])').forEach(el=>{el.disabled=true;});const message=document.createElement('p');message.className='awsw-notice';message.textContent=lang==='ur'?'یہ صرف ڈیزائن کا پیش منظر ہے۔ درخواست جمع نہیں ہو گی۔':'Design preview only. Form submissions are unavailable on GitHub Pages.';form.prepend(message);});
   document.querySelectorAll('style').forEach(el=>el.textContent=el.textContent.replaceAll(source,'https://shayanahmed226.github.io'+prefix.replace(/\/$/,'')));
   document.querySelectorAll('meta[property^="og:"],meta[name=generator],meta[name=robots]').forEach(el=>el.remove());
   const robots=document.createElement('meta');robots.name='robots';robots.content='noindex,nofollow';document.head.append(robots);
   document.title=document.title.replace(/AWS Welfare · Clean ZIP QA/g,'AWS Welfare').replace(/\s*\|?\s*AWS Welfare · Local QA/g,'AWS Welfare')+' — Client Preview';
   const banner=document.createElement('div');banner.className='awsw-announcement';banner.setAttribute('data-preview-banner','');banner.textContent=lang==='ur'?'کلائنٹ ڈیزائن پیش منظر · ادائیگیاں اور فارم جمع کرانا دستیاب نہیں':'CLIENT DESIGN PREVIEW · Payments and form submissions are not enabled';document.body.prepend(banner);
   document.querySelectorAll('.awsw-animate-in').forEach(e=>e.classList.remove('awsw-animate-in'));
  },{prefix,source,lang});
  const dest=path.join(out,lang==='ur'?'ur':'',slug);fs.mkdirSync(dest,{recursive:true});fs.writeFileSync(path.join(dest,'index.html'),(await p.content()).replace(/[ \t]+$/gm,''));
 }
 fs.writeFileSync(path.join(out,'.nojekyll'),'');fs.copyFileSync(path.join(out,'index.html'),path.join(out,'404.html'));
 await b.close();console.log(`Exported ${pages.length*2} English/Urdu pages from the actual WordPress theme.`);
})().catch(e=>{console.error(e);process.exit(1)});
