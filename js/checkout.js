(function () {
  'use strict';

  var form = document.querySelector('.js-checkout-form');
  if (!form) {
    return;
  }

  var evento = form.querySelector('#evento_id');
  var prefijo = form.querySelector('#prefijo');
  var numeracion = form.querySelector('#numeracion');
  var prefijoAddon = form.querySelector('.js-checkout-prefijo-addon');
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

  function obtenerPrefijosEvento() {
    if (!evento || !evento.value) {
      return [];
    }

    var opcion = evento.options[evento.selectedIndex];
    if (!opcion) {
      return [];
    }

    try {
      var lista = JSON.parse(opcion.getAttribute('data-prefijos') || '[]');
      return Array.isArray(lista) ? lista : [];
    } catch (e) {
      return [];
    }
  }

  function actualizarPrefijoAddon() {
    var valorPrefijo = prefijo && prefijo.value ? String(prefijo.value).toUpperCase() : '';
    if (!prefijoAddon) {
      return;
    }

    if (valorPrefijo) {
      prefijoAddon.textContent = valorPrefijo;
      prefijoAddon.classList.remove('d-none');
    } else {
      prefijoAddon.textContent = '';
      prefijoAddon.classList.add('d-none');
    }
  }

  function llenarPrefijosEvento() {
    var tieneEvento = !!(evento && evento.value);
    var prefijos = obtenerPrefijosEvento();
    var tienePrefijos = prefijos.length > 0;

    if (!prefijo) {
      return;
    }

    prefijo.innerHTML = '';
    var opcionInicial = document.createElement('option');

    if (!tieneEvento) {
      opcionInicial.value = '';
      opcionInicial.textContent = 'Seleccione evento primero…';
      prefijo.appendChild(opcionInicial);
      prefijo.disabled = true;
      prefijo.required = false;
      return;
    }

    if (!tienePrefijos) {
      opcionInicial.value = '';
      opcionInicial.textContent = 'Sin prefijo';
      prefijo.appendChild(opcionInicial);
      prefijo.disabled = true;
      prefijo.required = false;
      return;
    }

    opcionInicial.value = '';
    opcionInicial.textContent = 'Seleccione prefijo…';
    prefijo.appendChild(opcionInicial);
    prefijos.forEach(function (texto) {
      var opcion = document.createElement('option');
      opcion.value = String(texto);
      opcion.textContent = String(texto);
      prefijo.appendChild(opcion);
    });
    prefijo.disabled = false;
    prefijo.required = true;
  }

  function sincronizarNumeracion() {
    var tieneEvento = !!(evento && evento.value);
    var prefijos = obtenerPrefijosEvento();
    var tienePrefijos = prefijos.length > 0;
    var tienePrefijo = !!(prefijo && prefijo.value);

    if (numeracion) {
      var puedeNumerar = tieneEvento && (!tienePrefijos || tienePrefijo);
      numeracion.disabled = !puedeNumerar;
      numeracion.placeholder = !tieneEvento
        ? 'Seleccione evento primero…'
        : (tienePrefijos && !tienePrefijo
          ? 'Seleccione prefijo primero…'
          : 'Ingrese la numeración');
    }

    actualizarPrefijoAddon();
  }

  if (evento) {
    evento.addEventListener('change', function () {
      llenarPrefijosEvento();
      if (numeracion) {
        numeracion.value = '';
      }
      sincronizarNumeracion();
      mostrarError('');

      if (evento.value && prefijo && !prefijo.disabled) {
        prefijo.focus();
      } else if (evento.value && numeracion && !numeracion.disabled) {
        numeracion.focus();
      }
    });
  }

  if (prefijo) {
    prefijo.addEventListener('change', function () {
      if (numeracion) {
        numeracion.value = '';
      }
      sincronizarNumeracion();
      mostrarError('');

      if (numeracion && !numeracion.disabled) {
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

    if (prefijo && prefijo.required && !prefijo.value) {
      mostrarError('Selecciona un prefijo.');
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

  llenarPrefijosEvento();
  sincronizarNumeracion();
})();
