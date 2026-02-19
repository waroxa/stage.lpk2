(() => {
  const sheet = document.querySelector('.sheet');
  const overlay = document.querySelector('.sheet-overlay');
  if (!sheet || !overlay) return;

  const openButtons = document.querySelectorAll('[data-open-sheet]');
  const closeTarget = document.querySelector('[data-close-sheet]');

  const setOpen = (isOpen) => {
    sheet.classList.toggle('open', isOpen);
    overlay.classList.toggle('open', isOpen);
    sheet.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    document.body.style.overflow = isOpen ? 'hidden' : '';
  };

  openButtons.forEach((button) => button.addEventListener('click', () => setOpen(true)));
  closeTarget?.addEventListener('click', () => setOpen(false));

  const ticketRows = document.querySelectorAll('.ticket-row');
  const totalNode = document.querySelector('[data-total]');

  const updateTotal = () => {
    let total = 0;
    ticketRows.forEach((row) => {
      const count = Number(row.querySelector('output')?.textContent || 0);
      const price = Number(row.dataset.price || 0);
      total += count * price;
    });
    if (totalNode) totalNode.textContent = `${total} €`;
  };

  ticketRows.forEach((row) => {
    const output = row.querySelector('output');
    const plus = row.querySelector('[data-plus]');
    const minus = row.querySelector('[data-minus]');
    if (!output || !plus || !minus) return;

    plus.addEventListener('click', () => {
      output.textContent = String(Number(output.textContent || 0) + 1);
      updateTotal();
    });

    minus.addEventListener('click', () => {
      const current = Number(output.textContent || 0);
      output.textContent = String(Math.max(0, current - 1));
      updateTotal();
    });
  });
})();
