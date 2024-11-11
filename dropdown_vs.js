document.querySelectorAll('.faq-question').forEach(button => {
    button.addEventListener('click', () => {
      const faqItem = button.parentElement;
      faqItem.classList.toggle('active');
      button.querySelector('.icon').textContent = faqItem.classList.contains('active') ? '-' : '+';
    });
  });
  