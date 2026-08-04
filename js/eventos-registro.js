(function () {
  'use strict';

  function tipoCobroSeleccionado(contenedor, selector) {
    var marcado = contenedor.querySelector(selector + ':checked');
    return marcado ? marcado.value : 'pago';
  }

  function obtenerOpcionEvento(contenedor) {
    var eventoSelect = contenedor.querySelector('select[name="evento_id"]');
    if (!eventoSelect || !eventoSelect.value) {
      return null;
    }

    return eventoSelect.options[eventoSelect.selectedIndex] || null;
  }

  function obtenerTiposEntradaEvento(contenedor) {
    var opcion = obtenerOpcionEvento(contenedor);
    if (!opcion) {
      return null;
    }

    var raw = opcion.getAttribute('data-tipos-entrada') || '[]';
    try {
      var tipos = JSON.parse(raw);
      return Array.isArray(tipos) ? tipos : [];
    } catch (e) {
      return [];
    }
  }

  function obtenerValorEventoSeleccionado(contenedor) {
    var tipoSelect = contenedor.querySelector('.js-tipo-entrada-evento');
    if (tipoSelect && tipoSelect.value) {
      var opcionTipo = tipoSelect.options[tipoSelect.selectedIndex];
      if (opcionTipo) {
        return parseFloat(opcionTipo.getAttribute('data-valor') || '0');
      }
    }

    var tipos = obtenerTiposEntradaEvento(contenedor);
    if (tipos === null) {
      return null;
    }

    if (tipos.length === 1) {
      return parseFloat(tipos[0].valor || 0);
    }

    if (tipos.length > 1) {
      return null;
    }

    var opcion = obtenerOpcionEvento(contenedor);
    return opcion ? parseFloat(opcion.getAttribute('data-valor') || '0') : 0;
  }

  function leerValorNumerico(input, fallback) {
    if (!input || input.value === '') {
      return fallback;
    }
    var n = parseFloat(input.value);
    return isNaN(n) ? fallback : n;
  }

  function setVisible(elemento, visible) {
    if (!elemento) {
      return;
    }
    elemento.style.display = visible ? '' : 'none';
  }

  function actualizarSelectTiposEntrada(contenedor) {
    var campoTipo = contenedor.querySelector('.js-campo-tipo-entrada-evento');
    var tipoSelect = contenedor.querySelector('.js-tipo-entrada-evento');
    var tipos = obtenerTiposEntradaEvento(contenedor);
    var valorActual = tipoSelect ? tipoSelect.value : '';

    if (!campoTipo || !tipoSelect) {
      return;
    }

    // Siempre visible; solo se habilita con evento.
    setVisible(campoTipo, true);

    var sinEvento = tipos === null;
    var tieneTipos = !sinEvento && tipos.length > 0;

    if (sinEvento) {
      tipoSelect.innerHTML = '<option value="">Seleccione evento primero…</option>';
    } else if (!tieneTipos) {
      tipoSelect.innerHTML = '<option value="">Sin tipos de entrada</option>';
    } else {
      tipoSelect.innerHTML = '<option value="">Seleccione tipo…</option>';
      tipos.forEach(function (tipo) {
        var option = document.createElement('option');
        option.value = String(tipo.id || '');
        option.textContent = tipo.nombre || '';
        option.setAttribute('data-valor', String(tipo.valor != null ? tipo.valor : 0));
        if (String(tipo.id) === String(valorActual)) {
          option.selected = true;
        }
        tipoSelect.appendChild(option);
      });

      if (!tipoSelect.value && tipos.length === 1) {
        tipoSelect.value = String(tipos[0].id || '');
      }
    }

    tipoSelect.required = tieneTipos;
    tipoSelect.disabled = !tieneTipos;
  }

  function actualizarNumeracion(contenedor) {
    var eventoSelect = contenedor.querySelector('select[name="evento_id"]');
    var campoNumeracion = contenedor.querySelector('.js-campo-numeracion-evento');
    var inputNumeracion = contenedor.querySelector('input[name="numeracion"]');

    if (!campoNumeracion || !inputNumeracion) {
      return;
    }

    // Siempre visible; se habilita solo si el evento lo requiere.
    setVisible(campoNumeracion, true);

    var hayEvento = !!(eventoSelect && eventoSelect.value);
    var requiere = false;

    if (hayEvento) {
      var opcion = eventoSelect.options[eventoSelect.selectedIndex];
      requiere = !!(opcion && opcion.getAttribute('data-requiere-numeracion') === '1');
    }

    inputNumeracion.disabled = !requiere;
    inputNumeracion.required = requiere;
    inputNumeracion.placeholder = !hayEvento
      ? 'Seleccione evento primero…'
      : (requiere ? 'Obligatoria para este evento' : 'No aplica para este evento');

    if (!requiere) {
      inputNumeracion.value = '';
    }

    var label = campoNumeracion.querySelector('.form-label');
    if (label) {
      var marca = label.querySelector('.text-danger');
      if (requiere && !marca) {
        label.insertAdjacentHTML('beforeend', ' <span class="text-danger">*</span>');
      } else if (!requiere && marca) {
        marca.remove();
      }
    }
  }

  function actualizarBloquePagoEvento(contenedor, opciones) {
    opciones = opciones || {};
    var sugerirValor = !!opciones.sugerirValor;
    var valorCatalogo = obtenerValorEventoSeleccionado(contenedor);
    var tipos = obtenerTiposEntradaEvento(contenedor);
    var sinEvento = tipos === null;
    var tipoSelect = contenedor.querySelector('.js-tipo-entrada-evento');
    var sinTipo = !sinEvento && tipos.length > 0 && (!tipoSelect || !tipoSelect.value);
    var tipoListo = !sinEvento && !sinTipo;
    var campoValor = contenedor.querySelector('.js-campo-valor-evento');
    var bloqueFormaPago = contenedor.querySelector('.js-bloque-forma-pago-evento');
    var bloqueEstado = contenedor.querySelector('.js-bloque-estado-pago-evento');
    var bloquePagoLegacy = contenedor.querySelector('.js-bloque-pago-evento');
    var hiddenFormaPago = contenedor.querySelector('.js-forma-pago-gratuito');
    var hiddenValor = contenedor.querySelector('.js-valor-gratuito');
    var hiddenEstado = contenedor.querySelector('.js-estado-pago-gratuito');
    var valorInput = contenedor.querySelector('.js-valor-evento');
    var metodosPago = contenedor.querySelectorAll('.js-metodo-pago-evento');
    var estadosPago = contenedor.querySelectorAll('.js-estado-pago-evento');
    var pasosDespuesTipo = contenedor.querySelectorAll('.js-paso-despues-tipo');
    var botonSubmit = contenedor.querySelector('.js-submit-registro-evento');

    if (valorInput && sugerirValor && tipoListo && valorCatalogo !== null) {
      valorInput.value = valorCatalogo;
    } else if (valorInput && sugerirValor && (sinEvento || sinTipo)) {
      valorInput.value = '';
    }

    var valorActual = tipoListo
      ? leerValorNumerico(valorInput, valorCatalogo != null ? valorCatalogo : 0)
      : null;
    var esGratuito = tipoListo && valorActual !== null && valorActual <= 0;
    var esDePago = tipoListo && valorActual !== null && valorActual > 0;

    // Valor y datos del participante: visibles siempre; habilitados tras elegir tipo.
    setVisible(campoValor, true);
    pasosDespuesTipo.forEach(function (campo) {
      campo.disabled = !tipoListo;
      if (campo.classList.contains('js-valor-evento')) {
        campo.required = tipoListo;
        campo.min = '0';
        campo.placeholder = '0.00';
      }
    });

    // Forma de pago y estado: visibles solo si no es gratuito.
    setVisible(bloqueFormaPago, !esGratuito);
    setVisible(bloqueEstado, !esGratuito);
    if (bloquePagoLegacy) {
      setVisible(bloquePagoLegacy, !esGratuito);
    }

    if (hiddenFormaPago) {
      hiddenFormaPago.disabled = !esGratuito;
    }

    if (hiddenEstado) {
      hiddenEstado.disabled = !esGratuito;
    }

    if (hiddenValor) {
      // El valor visible se envía cuando está habilitado.
      hiddenValor.disabled = true;
    }

    metodosPago.forEach(function (radio) {
      radio.disabled = !esDePago;
      radio.required = esDePago;
    });

    estadosPago.forEach(function (radio) {
      radio.disabled = !esDePago;
      radio.required = esDePago;
    });

    if (botonSubmit) {
      botonSubmit.disabled = !tipoListo;
    }
  }

  function actualizarBotonesQuitarTipos(contenedor) {
    var filas = contenedor.querySelectorAll('.js-tipo-entrada-fila');
    filas.forEach(function (fila) {
      var boton = fila.querySelector('.js-quitar-tipo-entrada');
      if (boton) {
        boton.disabled = filas.length <= 1;
      }
    });
  }

  function actualizarBloqueValorCatalogo(contenedor) {
    var esGratuito = tipoCobroSeleccionado(contenedor, '.js-tipo-cobro-catalogo') === 'gratuito';

    contenedor.querySelectorAll('.js-tipo-entrada-fila').forEach(function (fila) {
      var campoValor = fila.querySelector('.js-campo-valor-tipo-entrada');
      var valorInput = fila.querySelector('.js-valor-tipo-entrada');
      var hiddenValor = fila.querySelector('.js-valor-tipo-entrada-gratuito');

      if (campoValor) {
        campoValor.style.display = '';
      }

      if (valorInput) {
        valorInput.min = '0';
        valorInput.disabled = esGratuito;
        valorInput.required = !esGratuito;
        if (esGratuito) {
          valorInput.value = '0';
          valorInput.removeAttribute('required');
        } else if (valorInput.value === '') {
          valorInput.value = '0';
        }
      }

      if (hiddenValor) {
        hiddenValor.disabled = !esGratuito;
      }
    });

    actualizarBotonesQuitarTipos(contenedor);
  }

  function crearFilaTipoEntrada(contenedor) {
    var lista = contenedor.querySelector('.js-tipos-entrada-lista');
    var plantilla = lista ? lista.querySelector('.js-tipo-entrada-fila') : null;
    if (!lista || !plantilla) {
      return;
    }

    var nueva = plantilla.cloneNode(true);
    nueva.querySelectorAll('input').forEach(function (input) {
      if (input.classList.contains('js-valor-tipo-entrada-gratuito')) {
        input.value = '0';
      } else if (input.classList.contains('js-valor-tipo-entrada')) {
        input.value = '0';
        input.min = '0';
      } else {
        input.value = '';
      }
    });

    lista.appendChild(nueva);
    actualizarBloqueValorCatalogo(contenedor);
  }

  function refrescarFormularioRegistro(contenedor, opciones) {
    actualizarSelectTiposEntrada(contenedor);
    actualizarNumeracion(contenedor);
    actualizarBloquePagoEvento(contenedor, opciones || {});
  }

  function inicializarContenedorRegistro(contenedor) {
    var eventoSelect = contenedor.querySelector('select[name="evento_id"]');
    var tipoSelect = contenedor.querySelector('.js-tipo-entrada-evento');

    if (eventoSelect) {
      eventoSelect.addEventListener('change', function () {
        refrescarFormularioRegistro(contenedor, { sugerirValor: true });
      });
    }

    if (tipoSelect) {
      tipoSelect.addEventListener('change', function () {
        actualizarBloquePagoEvento(contenedor, { sugerirValor: true });
      });
    }

    var valorInput = contenedor.querySelector('.js-valor-evento');
    if (valorInput && !valorInput.dataset.boundValorEvento) {
      valorInput.dataset.boundValorEvento = '1';
      valorInput.addEventListener('input', function () {
        actualizarBloquePagoEvento(contenedor, { sugerirValor: false });
      });
    }

    var sugerirAlInicio = !valorInput || valorInput.value === '';
    refrescarFormularioRegistro(contenedor, { sugerirValor: sugerirAlInicio });
  }

  function inicializarContenedorCatalogo(contenedor) {
    contenedor.querySelectorAll('.js-tipo-cobro-catalogo').forEach(function (radio) {
      radio.addEventListener('change', function () {
        actualizarBloqueValorCatalogo(contenedor);
      });
    });

    var botonAgregar = contenedor.querySelector('.js-agregar-tipo-entrada');
    if (botonAgregar && !botonAgregar.dataset.boundTipos) {
      botonAgregar.dataset.boundTipos = '1';
      botonAgregar.addEventListener('click', function () {
        crearFilaTipoEntrada(contenedor);
      });
    }

    if (!contenedor.dataset.boundQuitarTipos) {
      contenedor.dataset.boundQuitarTipos = '1';
      contenedor.addEventListener('click', function (event) {
        var boton = event.target.closest('.js-quitar-tipo-entrada');
        if (!boton || !contenedor.contains(boton)) {
          return;
        }

        var fila = boton.closest('.js-tipo-entrada-fila');
        var filas = contenedor.querySelectorAll('.js-tipo-entrada-fila');
        if (fila && filas.length > 1) {
          fila.remove();
          actualizarBotonesQuitarTipos(contenedor);
        }
      });
    }

    actualizarBloqueValorCatalogo(contenedor);
  }

  document.querySelectorAll('#formRegistroEvento, .modal-editar-registro form').forEach(function (form) {
    if (
      form.querySelector('select[name="evento_id"]')
      && (
        form.querySelector('.js-campo-valor-evento')
        || form.querySelector('.js-bloque-forma-pago-evento')
        || form.querySelector('.js-bloque-pago-evento')
        || form.querySelector('.js-campo-tipo-entrada-evento')
      )
    ) {
      inicializarContenedorRegistro(form);
    }
  });

  document.querySelectorAll('#formAgregarEvento, .modal').forEach(function (contenedor) {
    if (contenedor.querySelector('.js-tipo-cobro-catalogo') || contenedor.querySelector('.js-tipos-entrada-lista')) {
      inicializarContenedorCatalogo(contenedor);
    }
  });
})();
