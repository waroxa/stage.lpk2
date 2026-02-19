(() => {
  const templates = document.querySelectorAll('.lpk-template-3');
  if (!templates.length) return;

  templates.forEach((template) => {
    const sheet = template.querySelector('.sheet');
    const overlay = template.querySelector('.sheet-overlay');
    let closeSheet = () => {};

    if (sheet && overlay) {
      const openButtons = template.querySelectorAll('[data-open-sheet]');
      const closeTarget = template.querySelector('[data-close-sheet]');
      const handle = sheet.querySelector('.handle');
      const dragCloseThreshold = 80;
      let dragStartY = null;
      let dragDistance = 0;

      const bindActivate = (node, callback) => {
        if (!node) return;

        const activate = (event) => {
          if (event.type === 'keydown') {
            const isKeyboardActivate = event.key === 'Enter' || event.key === ' ';
            if (!isKeyboardActivate) return;
            event.preventDefault();
          }

          callback();
        };

        node.addEventListener('click', activate);
        node.addEventListener('pointerup', activate);
        node.addEventListener('touchend', activate, { passive: true });
        node.addEventListener('keydown', activate);
      };

      const setOpen = (isOpen) => {
        sheet.classList.toggle('open', isOpen);
        overlay.classList.toggle('open', isOpen);
        sheet.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        template.classList.toggle('is-sheet-open', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';

        if (!isOpen) {
          sheet.style.transform = '';
          sheet.classList.remove('is-dragging');
        }
      };

      const resetDragState = () => {
        dragStartY = null;
        dragDistance = 0;
        sheet.classList.remove('is-dragging');
        sheet.style.transform = '';
      };

      const onDragStart = (event) => {
        if (!sheet.classList.contains('open')) return;

        if (sheet.scrollTop > 0) return;

        dragStartY = event.touches[0].clientY;
        dragDistance = 0;
        sheet.classList.add('is-dragging');
      };

      const onDragMove = (event) => {
        if (dragStartY === null) return;

        const delta = event.touches[0].clientY - dragStartY;
        if (delta <= 0) {
          dragDistance = 0;
          sheet.style.transform = 'translate(-50%, 0)';
          return;
        }

        dragDistance = delta;
        sheet.style.transform = `translate(-50%, ${Math.min(delta, 200)}px)`;
      };

      const onDragEnd = () => {
        if (dragStartY === null) return;

        if (dragDistance > dragCloseThreshold) {
          resetDragState();
          setOpen(false);
          return;
        }

        resetDragState();
      };


      openButtons.forEach((button) => bindActivate(button, () => setOpen(true)));
      bindActivate(closeTarget, () => setOpen(false));
      bindActivate(handle, () => setOpen(false));

      sheet.addEventListener('touchstart', onDragStart, { passive: true });
      sheet.addEventListener('touchmove', onDragMove, { passive: true });
      sheet.addEventListener('touchend', onDragEnd);
    }

    const chips = Array.from(template.querySelectorAll('.filter-chips .chip[data-filter]'));
    const searchInput = template.querySelector('.search-input');
    const eventCards = Array.from(template.querySelectorAll('.event-card[data-event-filters]'));
    const emptyState = template.querySelector('.empty-state');

    if (chips.length && searchInput && eventCards.length) {
      let activeFilter = 'all';

      const updateListing = () => {
        const query = String(searchInput.value || '').trim().toLowerCase();
        let visibleCount = 0;

        eventCards.forEach((card) => {
          const filters = String(card.dataset.eventFilters || '').split(/\s+/).filter(Boolean);
          const searchBlob = String(card.dataset.eventSearch || '').toLowerCase();
          const matchesFilter = activeFilter === 'all' || filters.includes(activeFilter);
          const matchesSearch = !query || searchBlob.includes(query);
          const isVisible = matchesFilter && matchesSearch;

          card.hidden = !isVisible;
          if (isVisible) {
            visibleCount += 1;
          }
        });

        if (emptyState) {
          emptyState.hidden = visibleCount > 0;
        }
      };

      chips.forEach((chip) => {
        chip.addEventListener('click', () => {
          activeFilter = chip.dataset.filter || 'all';
          chips.forEach((item) => item.classList.toggle('is-active', item === chip));
          updateListing();
        });
      });

      searchInput.addEventListener('input', updateListing);
      updateListing();
    }

    const description = template.querySelector('[data-description-preview]');
    const descriptionToggle = template.querySelector('[data-description-toggle]');

    if (description && descriptionToggle) {
      requestAnimationFrame(() => {
        const requiresToggle = description.scrollHeight > description.clientHeight + 8 || description.scrollHeight > 155;

        if (!requiresToggle) {
          description.classList.remove('is-collapsed');
          descriptionToggle.hidden = true;
          return;
        }

        description.classList.add('is-collapsed');
        descriptionToggle.hidden = false;

        const readMoreLabel = descriptionToggle.textContent || 'Read more';
        const readLessLabel = descriptionToggle.dataset.lessLabel || 'Read less';

        descriptionToggle.addEventListener('click', () => {
          const collapsed = description.classList.toggle('is-collapsed');
          descriptionToggle.textContent = collapsed ? readMoreLabel : readLessLabel;
        });
      });
    }

    const ticketRows = Array.from(template.querySelectorAll('.ticket-row'));
    const totalNode = template.querySelector('[data-total]');
    const currencyCode = template.dataset.currencyCode || 'CAD';
    const currencyLocale = template.dataset.currencyLocale || 'fr-CA';
    const sheetForm = template.querySelector('.sheet-form');
    const checkoutUrl = template.dataset.checkoutUrl || '/checkout/';
    const addToCartUrl = template.dataset.addToCartUrl || `${window.location.origin}/?wc-ajax=add_to_cart`;
    const submitButtons = sheetForm
      ? Array.from(sheetForm.querySelectorAll('.tribe-tickets__tickets-buy'))
      : [];

    const formatCurrency = (amount) => {
      try {
        return new Intl.NumberFormat(currencyLocale, {
          style: 'currency',
          currency: currencyCode,
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        }).format(amount);
      } catch (error) {
        return `${amount.toFixed(2)} ${currencyCode}`;
      }
    };

    const findTicketQuantityInput = (ticketId) => {
      if (!sheetForm || !ticketId) return null;

      const ticketItems = Array.from(sheetForm.querySelectorAll('[data-ticket-id]'));
      const ticketItem = ticketItems.find((item) => String(item.dataset.ticketId || '') === String(ticketId));
      const directInput = ticketItem?.querySelector('.tribe-tickets__tickets-item-quantity-number-input');

      if (directInput) {
        return directInput;
      }

      const fallbackInputs = Array.from(sheetForm.querySelectorAll('.tribe-tickets__tickets-item-quantity-number-input'));
      const byId = fallbackInputs.find((input) => String(input.id || '').endsWith(`--${ticketId}`));
      if (byId) return byId;

      return null;
    };

    const syncTicketQuantity = (ticketId, quantity) => {
      if (!sheetForm || !ticketId) return;

      const quantityInput = findTicketQuantityInput(ticketId);

      if (!quantityInput) return;

      quantityInput.value = String(Math.max(0, quantity));
      quantityInput.dispatchEvent(new Event('input', { bubbles: true }));
      quantityInput.dispatchEvent(new Event('change', { bubbles: true }));
      quantityInput.dispatchEvent(new KeyboardEvent('keyup', { bubbles: true }));
    };

    const getSelectedTicketCount = () => {
      return ticketRows.reduce((count, row) => {
        return count + Number(row.querySelector('output')?.textContent || 0);
      }, 0);
    };

    const syncSubmitState = () => {
      const hasSelection = getSelectedTicketCount() > 0;

      submitButtons.forEach((button) => {
        button.disabled = !hasSelection;
        button.setAttribute('aria-disabled', hasSelection ? 'false' : 'true');
      });
    };

    const syncAllTicketQuantities = () => {
      ticketRows.forEach((row) => {
        const ticketId = row.dataset.ticketId || '';
        const quantity = Number(row.querySelector('output')?.textContent || 0);
        syncTicketQuantity(ticketId, quantity);
      });
    };

    const selectedTickets = () => {
      return ticketRows
        .map((row) => {
          const ticketId = Number(row.dataset.ticketId || 0);
          const quantity = Number(row.querySelector('output')?.textContent || 0);

          if (!ticketId || quantity <= 0) {
            return null;
          }

          return {
            ticketId,
            quantity,
          };
        })
        .filter(Boolean);
    };

    const addTicketToCart = async ({ ticketId, quantity }) => {
      const payload = new URLSearchParams({
        product_id: String(ticketId),
        quantity: String(quantity),
      });

      const response = await fetch(addToCartUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: payload.toString(),
      });

      if (!response.ok) {
        throw new Error(`Unable to add ticket ${ticketId} to cart.`);
      }
    };

    const sendSelectionToCheckout = async () => {
      const selected = selectedTickets();

      if (!selected.length) {
        syncSubmitState();
        return;
      }

      for (const ticket of selected) {
        // eslint-disable-next-line no-await-in-loop
        await addTicketToCart(ticket);
      }

      window.location.href = checkoutUrl;
    };

    const updateSubmitButtonState = (isLoading) => {
      submitButtons.forEach((button) => {
        if (isLoading) {
          button.setAttribute('data-label-before-submit', button.textContent || '');
          button.textContent = '...';
          button.disabled = true;
          button.setAttribute('aria-disabled', 'true');
          return;
        }

        const originalLabel = button.getAttribute('data-label-before-submit');
        if (originalLabel) {
          button.textContent = originalLabel;
          button.removeAttribute('data-label-before-submit');
        }
      });

      syncSubmitState();
    };

    const updateTotal = () => {
      let total = 0;
      ticketRows.forEach((row) => {
        const count = Number(row.querySelector('output')?.textContent || 0);
        const price = Number(row.dataset.price || 0);
        total += count * price;
      });
      if (totalNode) {
        totalNode.textContent = formatCurrency(Math.round(total * 100) / 100);
      }
    };

    ticketRows.forEach((row) => {
      const output = row.querySelector('output');
      const plus = row.querySelector('[data-plus]');
      const minus = row.querySelector('[data-minus]');
      const ticketId = row.dataset.ticketId || '';

      if (!output || !plus || !minus) return;

      plus.addEventListener('click', () => {
        const nextValue = Number(output.textContent || 0) + 1;
        output.textContent = String(nextValue);
        syncTicketQuantity(ticketId, nextValue);
        updateTotal();
        syncSubmitState();
      });

      minus.addEventListener('click', () => {
        const current = Number(output.textContent || 0);
        const nextValue = Math.max(0, current - 1);
        output.textContent = String(nextValue);
        syncTicketQuantity(ticketId, nextValue);
        updateTotal();
        syncSubmitState();
      });
    });

    submitButtons.forEach((button) => {
      button.addEventListener('click', async (event) => {
        event.preventDefault();
        event.stopImmediatePropagation();

        syncAllTicketQuantities();
        syncSubmitState();

        if (button.disabled) {
          return;
        }

        updateSubmitButtonState(true);

        try {
          await sendSelectionToCheckout();
        } catch (error) {
          const form = button.form;

          if (form) {
            form.submit();
            return;
          }
        } finally {
          updateSubmitButtonState(false);
        }
      }, true);
    });

    updateTotal();
    syncSubmitState();
  });
})();
