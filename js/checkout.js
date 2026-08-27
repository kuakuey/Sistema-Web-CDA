(function () {
  'use strict';

  var form = document.querySelector('.js-checkout-form');
  if (!form) {
    return;
  }

  var evento = form.querySelector('#evento_id');
  var numeracion = form.querySelector('#numeracion');
  var boton = form.querySelector('.js-checkout-submit');
  var alerta = document.querySelector('.js-checkout-error');
  var modalEl = document.getElementById('checkoutResultado');
  var modalTitulo = document.getElementById('checkoutResultadoTitulo');
  var modalCuerpo = document.querySelector('.js-checkout-modal-cuerpo');
  var modal = modalEl && window.bootstrap ? new bootstrap.Modal(modalEl) : null;

  function escapeHtml(texto) {
    return String(texto == null ? '' : texto)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function mostrarError(mensaje) {
    if (!alerta) {
      return;
    }

    alerta.textContent = mensaje || '';
    alerta.classList.toggle('d-none', !mensaje);
  }

  function filaDato(etiqueta, valor) {
    return (
      '<div class="checkout-modal__fila">' +
        '<dt>' + escapeHtml(etiqueta) + '</dt>' +
        '<dd>' + escapeHtml(valor || '—') + '</dd>' +
      '</div>'
    );
  }

  function htmlBotonAsistencia(ticket) {
    if (ticket.asistio) {
      return (
        '<button type="button" class="btn btn-success w-100" disabled>' +
          '<i class="bi bi-check2-circle me-1"></i>Asistencia marcada' +
        '</button>'
      );
    }

    return (
      '<button type="button" class="btn btn-primary w-100 js-marcar-asistencia" data-id="' +
        escapeHtml(ticket.id) +
      '">' +
        '<i class="bi bi-person-check me-1"></i>Marcar asistencia' +
      '</button>'
    );
  }

  function htmlTicket(ticket) {
    return (
      '<div class="checkout-modal__ticket js-checkout-ticket" data-id="' + escapeHtml(ticket.id) + '">' +
        '<dl class="checkout-modal__datos mb-3">' +
          filaDato('Nombre', ticket.nombre) +
          filaDato('Numeración', ticket.numeracion) +
          filaDato('Tipo de entrada', ticket.tipo_entrada) +
          filaDato('Estado', ticket.estado) +
          filaDato('Asistencia', ticket.asistencia) +
        '</dl>' +
        '<div class="checkout-modal__acciones">' +
          htmlBotonAsistencia(ticket) +
        '</div>' +
      '</div>'
    );
  }

  function mostrarPopup(datos) {
    var tickets = datos.tickets || [];
    var repetido = !!datos.repetido;
    var nombres = (datos.nombres || []).filter(Boolean);
    var html = '';

    if (modalTitulo) {
      modalTitulo.textContent = repetido ? 'Numeración repetida' : 'Ticket';
    }

    if (repetido) {
      html +=
        '<div class="alert alert-warning mb-3">' +
          '<strong>Esta numeración está repetida.</strong> ' +
          (nombres.length
            ? 'Corresponde a: ' + nombres.map(escapeHtml).join(', ') + '.'
            : 'Hay más de un registro con el mismo número.') +
        '</div>';
    }

    tickets.forEach(function (ticket, indice) {
      if (indice > 0) {
        html += '<hr class="my-3">';
      }
      html += htmlTicket(ticket);
    });

    if (modalCuerpo) {
      modalCuerpo.innerHTML = html;
    }

    if (modal) {
      modal.show();
    }
  }

  function actualizarTicketEnPopup(ticket) {
    if (!modalCuerpo || !ticket) {
      return;
    }

    var bloque = modalCuerpo.querySelector('.js-checkout-ticket[data-id="' + ticket.id + '"]');
    if (!bloque) {
      return;
    }

    var wrapper = document.createElement('div');
    wrapper.innerHTML = htmlTicket(ticket);
    var nuevo = wrapper.firstElementChild;
    if (nuevo) {
      bloque.replaceWith(nuevo);
    }
  }

  function marcarAsistencia(botonAsistencia) {
    var ticketId = botonAsistencia.getAttribute('data-id');
    if (!ticketId) {
      return;
    }

    botonAsistencia.disabled = true;

    var cuerpo = new FormData();
    cuerpo.append('accion', 'marcar_asistencia');
    cuerpo.append('id', ticketId);

    fetch('checkout.php', {
      method: 'POST',
      body: cuerpo,
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
      .then(function (respuesta) {
        return respuesta.json();
      })
      .then(function (datos) {
        if (!datos || !datos.ok || !datos.ticket) {
          botonAsistencia.disabled = false;
          window.alert((datos && datos.error) || 'No se pudo marcar la asistencia.');
          return;
        }

        actualizarTicketEnPopup(datos.ticket);
      })
      .catch(function () {
        botonAsistencia.disabled = false;
        window.alert('No se pudo marcar la asistencia. Intenta de nuevo.');
      });
  }

  function sincronizarNumeracion() {
    if (!numeracion) {
      return;
    }

    var tieneEvento = !!(evento && evento.value);
    numeracion.disabled = !tieneEvento;
    numeracion.placeholder = tieneEvento
      ? 'Ingrese el código (ej. G203)'
      : 'Seleccione evento primero…';
  }

  if (evento) {
    evento.addEventListener('change', function () {
      sincronizarNumeracion();
      mostrarError('');

      if (evento.value && numeracion) {
        numeracion.value = '';
        numeracion.focus();
      }
    });
  }

  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', function () {
      if (numeracion && !numeracion.disabled) {
        numeracion.value = '';
        numeracion.focus();
      }
    });
  }

  if (modalCuerpo) {
    modalCuerpo.addEventListener('click', function (e) {
      var botonAsistencia = e.target.closest('.js-marcar-asistencia');
      if (!botonAsistencia) {
        return;
      }

      marcarAsistencia(botonAsistencia);
    });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    mostrarError('');

    if (!evento || !evento.value) {
      mostrarError('Selecciona un evento.');
      return;
    }

    if (!numeracion || numeracion.value.trim() === '') {
      mostrarError('Ingresa la numeración del ticket.');
      return;
    }

    var cuerpo = new FormData(form);
    cuerpo.append('accion', 'consultar');
    if (boton) {
      boton.disabled = true;
    }

    fetch('checkout.php', {
      method: 'POST',
      body: cuerpo,
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
      .then(function (respuesta) {
        return respuesta.json();
      })
      .then(function (datos) {
        if (!datos || !datos.ok) {
          mostrarError((datos && datos.error) || 'No se encontró el ticket.');
          return;
        }

        mostrarPopup(datos);
      })
      .catch(function () {
        mostrarError('No se pudo consultar el ticket. Intenta de nuevo.');
      })
      .then(function () {
        if (boton) {
          boton.disabled = false;
        }
      });
  });

  sincronizarNumeracion();
})();
