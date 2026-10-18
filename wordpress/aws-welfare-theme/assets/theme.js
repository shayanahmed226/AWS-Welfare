(() => {
 document.documentElement.classList.add('awsw-js');
 const button = document.querySelector('.awsw-menu-toggle');
 if (button) { button.addEventListener('click', () => { const open = button.getAttribute('aria-expanded') !== 'true'; button.setAttribute('aria-expanded', String(open)); document.querySelector('.awsw-header').classList.toggle('awsw-menu-open', open); }); }
 if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) { const observer = new IntersectionObserver(entries => entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('awsw-animate-in'); observer.unobserve(entry.target); } }), {threshold: .08}); document.querySelectorAll('.awsw-section').forEach(el => observer.observe(el)); }
})();
