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
  var campoNombre = document.getElementById('ticket_nombre');
  var campoNumeracion = document.getElementById('ticket_numeracion');
  var campoTipo = document.getElementById('ticket_tipo');
  var campoEstado = document.getElementById('ticket_estado');

  function mostrarError(mensaje) {
    if (!alerta) {
      return;
    }

    alerta.textContent = mensaje || '';
    alerta.classList.toggle('d-none', !mensaje);
  }

  function llenarTicket(ticket) {
    var datos = ticket || {};
    if (campoNombre) {
      campoNombre.value = datos.nombre || '';
    }
    if (campoNumeracion) {
      campoNumeracion.value = datos.numeracion || '';
    }
    if (campoTipo) {
      campoTipo.value = datos.tipo_entrada || '';
    }
    if (campoEstado) {
      campoEstado.value = datos.estado || '';
    }
  }

  function limpiarTicket() {
    llenarTicket({});
  }

  function sincronizarNumeracion() {
    if (!numeracion) {
      return;
    }

    var tieneEvento = !!(evento && evento.value);
    numeracion.disabled = !tieneEvento;
    numeracion.placeholder = tieneEvento
      ? 'Ingrese la numeración'
      : 'Seleccione evento primero…';
  }

  if (evento) {
    evento.addEventListener('change', function () {
      sincronizarNumeracion();
      limpiarTicket();
      mostrarError('');

      if (evento.value && numeracion) {
        numeracion.value = '';
        numeracion.focus();
      }
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
          limpiarTicket();
          mostrarError((datos && datos.error) || 'No se encontró el ticket.');
          return;
        }

        llenarTicket(datos.ticket);
        if (numeracion) {
          numeracion.value = '';
          numeracion.focus();
        }
      })
      .catch(function () {
        limpiarTicket();
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
