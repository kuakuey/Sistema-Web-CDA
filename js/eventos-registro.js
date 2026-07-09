(function () {
  'use strict';

  function tipoCobroSeleccionado(contenedor, selector) {
    var marcado = contenedor.querySelector(selector + ':checked');
    return marcado ? marcado.value : 'pago';
  }

  function obtenerValorEventoSeleccionado(contenedor) {
    var eventoSelect = contenedor.querySelector('select[name="evento_id"]');
    if (!eventoSelect || !eventoSelect.value) {
      return null;
    }

    var opcion = eventoSelect.options[eventoSelect.selectedIndex];
    return opcion ? parseFloat(opcion.getAttribute('data-valor') || '0') : 0;
  }

  function alternarCampoReservado(elemento, visible) {
    if (!elemento) {
      return;
    }

    elemento.style.display = '';
    elemento.classList.toggle('invisible', !visible);
    elemento.setAttribute('aria-hidden', visible ? 'false' : 'true');
  }

  function actualizarBloquePagoEvento(contenedor) {
    var valorEvento = obtenerValorEventoSeleccionado(contenedor);
    var sinEvento = valorEvento === null;
    var esGratuito = !sinEvento && valorEvento <= 0;
    var campoValor = contenedor.querySelector('.js-campo-valor-evento');
    var bloqueFormaPago = contenedor.querySelector('.js-bloque-forma-pago-evento');
    var bloquePagoLegacy = contenedor.querySelector('.js-bloque-pago-evento');
    var hiddenFormaPago = contenedor.querySelector('.js-forma-pago-gratuito');
    var hiddenValor = contenedor.querySelector('.js-valor-gratuito');
    var valorInput = contenedor.querySelector('.js-valor-evento');
    var metodosPago = contenedor.querySelectorAll('.js-metodo-pago-evento');
    var mostrarPago = !esGratuito && !sinEvento;

    if (campoValor) {
      alternarCampoReservado(campoValor, mostrarPago);
    }

    if (bloqueFormaPago) {
      bloqueFormaPago.style.display = mostrarPago ? '' : 'none';
    }

    if (bloquePagoLegacy) {
      bloquePagoLegacy.style.display = mostrarPago ? '' : 'none';
    }

    if (hiddenFormaPago) {
      hiddenFormaPago.disabled = !esGratuito;
    }

    if (hiddenValor) {
      hiddenValor.disabled = !esGratuito;
    }

    if (valorInput) {
      valorInput.disabled = esGratuito || sinEvento;
      valorInput.required = mostrarPago;

      if (mostrarPago && valorEvento > 0) {
        if (!valorInput.value || parseFloat(valorInput.value) <= 0) {
          valorInput.value = valorEvento;
        }
      }
    }

    metodosPago.forEach(function (radio) {
      radio.disabled = !mostrarPago;
      radio.required = mostrarPago;
    });
  }

  function actualizarBloqueValorCatalogo(contenedor) {
    var esGratuito = tipoCobroSeleccionado(contenedor, '.js-tipo-cobro-catalogo') === 'gratuito';
    var bloqueValor = contenedor.querySelector('.js-bloque-valor-catalogo');
    var hiddenValor = contenedor.querySelector('.js-valor-catalogo-gratuito');
    var valorInput = bloqueValor ? bloqueValor.querySelector('input[name="valor"]:not(.js-valor-catalogo-gratuito)') : null;

    if (bloqueValor) {
      bloqueValor.style.display = esGratuito ? 'none' : '';
    }

    if (hiddenValor) {
      hiddenValor.disabled = !esGratuito;
    }

    if (valorInput) {
      valorInput.disabled = esGratuito;
      valorInput.required = !esGratuito;
    }
  }

  function actualizarNumeracion(contenedor) {
    var eventoSelect = contenedor.querySelector('select[name="evento_id"]');
    var campoNumeracion = contenedor.querySelector('.js-campo-numeracion-evento');
    var inputNumeracion = contenedor.querySelector('input[name="numeracion"]');

    if (!campoNumeracion) {
      return;
    }

    var requiere = false;

    if (eventoSelect && eventoSelect.value) {
      var opcion = eventoSelect.options[eventoSelect.selectedIndex];
      requiere = opcion && opcion.getAttribute('data-requiere-numeracion') === '1';
    } else if (inputNumeracion && inputNumeracion.value.trim() !== '') {
      requiere = true;
    }

    alternarCampoReservado(campoNumeracion, requiere);

    if (inputNumeracion) {
      inputNumeracion.required = requiere;
      if (!requiere) {
        inputNumeracion.value = '';
      }
    }
  }

  function inicializarContenedorRegistro(contenedor) {
    var eventoSelect = contenedor.querySelector('select[name="evento_id"]');

    if (eventoSelect) {
      eventoSelect.addEventListener('change', function () {
        actualizarNumeracion(contenedor);
        actualizarBloquePagoEvento(contenedor);
      });
    }

    actualizarNumeracion(contenedor);
    actualizarBloquePagoEvento(contenedor);
  }

  function inicializarContenedorCatalogo(contenedor) {
    contenedor.querySelectorAll('.js-tipo-cobro-catalogo').forEach(function (radio) {
      radio.addEventListener('change', function () {
        actualizarBloqueValorCatalogo(contenedor);
      });
    });

    actualizarBloqueValorCatalogo(contenedor);
  }

  document.querySelectorAll('#formRegistroEvento, .modal-editar-registro form').forEach(function (form) {
    if (form.querySelector('select[name="evento_id"]') && (form.querySelector('.js-campo-valor-evento') || form.querySelector('.js-bloque-forma-pago-evento') || form.querySelector('.js-bloque-pago-evento'))) {
      inicializarContenedorRegistro(form);
    }
  });

  document.querySelectorAll('#formAgregarEvento, .modal').forEach(function (contenedor) {
    if (contenedor.querySelector('.js-tipo-cobro-catalogo')) {
      inicializarContenedorCatalogo(contenedor);
    }
  });
})();
