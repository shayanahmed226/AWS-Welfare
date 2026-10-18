(() => {
 document.documentElement.classList.add('awsw-js');
 const button=document.querySelector('.awsw-menu-toggle');const header=document.querySelector('.awsw-header');
 if(button)button.addEventListener('click',()=>{const open=button.getAttribute('aria-expanded')!=='true';button.setAttribute('aria-expanded',String(open));header.classList.toggle('awsw-menu-open',open);});
 const groups=[...document.querySelectorAll('.awsw-nav-group')];groups.forEach(group=>group.addEventListener('toggle',()=>{group.querySelector('summary').setAttribute('aria-expanded',String(group.open));if(group.open&&innerWidth>780)groups.forEach(g=>{if(g!==group)g.open=false;});}));
 document.addEventListener('click',e=>{if(!header?.contains(e.target))groups.forEach(g=>g.open=false);});document.addEventListener('keydown',e=>{if(e.key==='Escape'){const active=groups.find(g=>g.open&&g.contains(document.activeElement));groups.forEach(g=>g.open=false);if(active)active.querySelector('summary').focus();else if(button&&header.classList.contains('awsw-menu-open')){header.classList.remove('awsw-menu-open');button.setAttribute('aria-expanded','false');button.focus();}}});
 if('IntersectionObserver' in window&&!matchMedia('(prefers-reduced-motion: reduce)').matches){const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('awsw-animate-in');observer.unobserve(e.target);}}),{threshold:.08});document.querySelectorAll('.awsw-section').forEach(el=>observer.observe(el));}
})();
