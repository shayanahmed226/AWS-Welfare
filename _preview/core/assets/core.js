(() => {
 const ur = document.documentElement.lang.startsWith('ur');
 const amount = document.querySelector('#awsw-amount');
 document.querySelectorAll('[data-awsw-amount]').forEach(button => { button.setAttribute('aria-pressed', 'false'); button.addEventListener('click', () => { if (!amount) return; amount.value = button.dataset.awswAmount; document.querySelectorAll('[data-awsw-amount]').forEach(b => b.setAttribute('aria-pressed', String(b === button))); amount.focus(); }); });
 if (amount) amount.addEventListener('input', () => document.querySelectorAll('[data-awsw-amount]').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.awswAmount === amount.value))));
 document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => { const target = document.getElementById(button.dataset.copy); const status = document.querySelector('.awsw-live'); try { await navigator.clipboard.writeText(target.textContent); if (status) status.textContent = ur ? 'معلومات کاپی ہو گئیں۔' : 'Bank details copied.'; } catch { if (status) status.textContent = ur ? 'کاپی دستیاب نہیں۔ معلومات منتخب کر کے کاپی کریں۔' : 'Copy is unavailable. Select and copy the bank details manually.'; } }));
})();
