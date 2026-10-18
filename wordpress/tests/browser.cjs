const { chromium } = require('playwright');
const fs = require('fs');
const {execFileSync} = require('child_process');
const base = 'http://localhost:8088';
const wp = code => execFileSync('docker',['exec','awsw-wp','php','/tmp/wp-cli.phar','eval',code,'--allow-root','--path=/var/www/html'],{encoding:'utf8'}).trim();
let assertions=0;
const assert = (condition, message) => { if (!condition) throw new Error(message); assertions++; console.log('PASS: '+message); };
(async()=>{
 const browser = await chromium.launch({executablePath:'/usr/bin/chromium',headless:true,args:['--no-sandbox']});
 const page = await browser.newPage(); const jsErrors=[];page.on('pageerror',e=>jsErrors.push(e.message));
 await page.goto(base+'/donate/');assert(await page.getByText('Bank transfer donations are not available yet.',{exact:false}).isVisible(),'missing-bank public flow disabled');assert(await page.locator('input[name="amount"]').count()===0,'missing bank does not expose submission form');
 // Synthetic, explicitly local-only bank fixtures. Never use these outside QA.
 wp('update_option("awsw_settings",["bank_title"=>"LOCAL QA ONLY — NOT A REAL ACCOUNT","bank_name"=>"Meezan Bank — LOCAL QA ONLY","bank_account"=>"0000000000000000"]);');
 for(const lang of ['en','ur']){
  for(const width of [360,390,768,1440]){
   await page.setViewportSize({width,height:900});await page.goto(base+'/?lang='+lang);await page.locator('.awsw-hero h1').waitFor();await page.evaluate(()=>document.fonts.ready);
   assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`${lang} home no overflow at ${width}px`);
   assert(await page.locator('html').getAttribute('dir')===(lang==='ur'?'rtl':'ltr'),`${lang} correct direction at ${width}px`);
  }
 }
 await page.goto(base+'/donate/?lang=ur');await page.locator('.awsw-language').click();assert(page.url().includes('/donate/')&&page.url().includes('lang=en'),'language switch retains donation page');
 await page.setViewportSize({width:390,height:844});await page.locator('.awsw-menu-toggle').click();assert(await page.locator('#awsw-navigation').isVisible(),'mobile menu opens with button');
 for(const path of ['about','programs','food','education','water','medical','campaigns','impact','transparency','volunteer','contact','payment-cancelled']){const response=await page.goto(base+'/'+path+'/?lang=ur');assert(response.status()===200&&await page.locator('h1').count()>0,'Urdu public page '+path);}
 await page.goto(base+'/campaigns/?program=water&campaign_status=active&campaign_search=LOCAL');assert(await page.locator('.awsw-campaigns .awsw-card').count()>=1,'campaign search/program/status filters');
 await page.goto(base+'/donate/?lang=en');const fields=await page.locator('.awsw-form').evaluate(f=>Object.fromEntries(new FormData(f)));fields.amount='100.50';fields.donor_name='Local QA donor';fields.donor_email='donor@example.invalid';fields.transfer_ref='QA-BROWSER-'+Date.now();fields.consent='1';
 // CSRF and validation errors must be rejected server side.
 const bad=await page.request.post(base+'/wp-admin/admin-post.php?lang=en',{form:{...fields,amount:'1e3'}});assert(bad.status()===400,'invalid amount rejected server-side');
 const csrf=await page.request.post(base+'/wp-admin/admin-post.php?lang=en',{form:{...fields,_awsw_nonce:'bad'}});assert(csrf.status()===403,'invalid CSRF nonce denied');
 const valid=await page.request.post(base+'/wp-admin/admin-post.php?lang=en',{form:fields,maxRedirects:0});assert(valid.status()===302&&valid.headers().location.includes('donation-confirmation'),'valid donation stored and redirected');
 const again=await page.request.post(base+'/wp-admin/admin-post.php?lang=en',{form:fields,maxRedirects:0});assert(again.status()===302&&again.headers().location===valid.headers().location,'identical submission returns same reference');
 await page.goto(base+'/donate/?lang=en');const other=await page.locator('.awsw-form').evaluate(f=>Object.fromEntries(new FormData(f)));const duplicate=await page.request.post(base+'/wp-admin/admin-post.php?lang=en',{form:{...fields,submission:other.submission,signature:other.signature}});assert(duplicate.status()===409,'new form cannot reuse bank transaction reference');
 const donationId=wp('global $wpdb;echo $wpdb->get_var("SELECT MAX(id) FROM {$wpdb->prefix}awsw_donations");');assert(wp('echo awsw_donation(' + Number(donationId) + ')->status;')==='pending','new web donation remains pending');
 const proof=await page.request.get(base+'/wp-admin/admin-post.php?action=awsw_proof&id='+donationId+'&_wpnonce=bad');assert(proof.status()===400||proof.status()===403,'anonymous private-proof endpoint denied');
 wp('global $wpdb; $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE \'awsw_rate_%\'"); wp_cache_flush();');
 for(const kind of ['volunteer','contact']){
  await page.goto(base+'/'+kind+'/?lang=ur');const values=await page.locator('.awsw-form').evaluate(f=>Object.fromEntries(new FormData(f)));Object.assign(values,{name:'مقامی جانچ',email:'enquiry@example.invalid',phone:'00000000000',city:'Local QA',interests:'water',availability:'Local QA only',message:'Local QA only',consent:'1'});
  const response=await page.request.post(base+'/wp-admin/admin-post.php?lang=ur',{form:values,maxRedirects:0});assert(response.status()===302&&response.headers().location.includes('awsw_notice=saved'),kind+' form saved in Urdu');
  wp('global $wpdb; $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE \'awsw_rate_%\'"); wp_cache_flush();');
 }
 const nojs=await browser.newContext({javaScriptEnabled:false,viewport:{width:360,height:800}});const np=await nojs.newPage();await np.goto(base+'/?lang=ur');assert(await np.locator('.awsw-hero h1').isVisible()&&await np.locator('#awsw-navigation').isVisible(),'essential Urdu content/navigation visible without JavaScript');await np.goto(base+'/donate/?lang=ur');assert(await np.locator('button[type=submit]').isVisible(),'donation submission available without JavaScript');await nojs.close();
 await page.goto(base+'/does-not-exist/');assert(await page.locator('h1').textContent()==='404','helpful 404 template');await page.goto(base+'/?s=LOCAL&lang=ur');assert(await page.locator('h1').count()>0,'search results page');
 await page.goto(base+'/?lang=en');await page.screenshot({path:'/tmp/awsw-desktop.png',fullPage:true});await page.setViewportSize({width:390,height:844});await page.goto(base+'/?lang=ur');await page.screenshot({path:'/tmp/awsw-urdu-mobile.png',fullPage:true});assert(jsErrors.length===0,'no JavaScript runtime errors');console.log('PUBLIC BROWSER: '+assertions+' assertions passed.');await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
