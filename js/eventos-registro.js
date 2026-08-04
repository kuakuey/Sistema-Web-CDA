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

  function alternarCampoReservado(elemento, visible) {
    if (!elemento) {
      return;
    }

    elemento.style.display = '';
    elemento.classList.toggle('invisible', !visible);
    elemento.setAttribute('aria-hidden', visible ? 'false' : 'true');
  }

  function actualizarSelectTiposEntrada(contenedor) {
    var campoTipo = contenedor.querySelector('.js-campo-tipo-entrada-evento');
    var tipoSelect = contenedor.querySelector('.js-tipo-entrada-evento');
    var tipos = obtenerTiposEntradaEvento(contenedor);
    var valorActual = tipoSelect ? tipoSelect.value : '';

    if (!campoTipo || !tipoSelect) {
      return;
    }

    var sinEvento = tipos === null;
    var tieneTipos = !sinEvento && tipos.length > 0;
    var siempreVisible = !campoTipo.classList.contains('invisible') && !campoTipo.hasAttribute('aria-hidden');

    if (siempreVisible) {
      campoTipo.style.display = '';
      campoTipo.classList.remove('invisible');
      campoTipo.setAttribute('aria-hidden', 'false');
    } else if (campoTipo.classList.contains('invisible') || campoTipo.hasAttribute('aria-hidden')) {
      alternarCampoReservado(campoTipo, tieneTipos);
    } else {
      campoTipo.style.display = tieneTipos ? '' : 'none';
    }

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

  function actualizarBloquePagoEvento(contenedor, opciones) {
    opciones = opciones || {};
    var sugerirValor = !!opciones.sugerirValor;
    var valorEvento = obtenerValorEventoSeleccionado(contenedor);
    var tipos = obtenerTiposEntradaEvento(contenedor);
    var sinEvento = tipos === null;
    var tipoSelect = contenedor.querySelector('.js-tipo-entrada-evento');
    var sinTipo = !sinEvento && tipos.length > 0 && (!tipoSelect || !tipoSelect.value);
    var esGratuito = !sinEvento && !sinTipo && valorEvento !== null && valorEvento <= 0;
    var campoValor = contenedor.querySelector('.js-campo-valor-evento');
    var bloqueFormaPago = contenedor.querySelector('.js-bloque-forma-pago-evento');
    var bloquePagoLegacy = contenedor.querySelector('.js-bloque-pago-evento');
    var hiddenFormaPago = contenedor.querySelector('.js-forma-pago-gratuito');
    var hiddenValor = contenedor.querySelector('.js-valor-gratuito');
    var valorInput = contenedor.querySelector('.js-valor-evento');
    var metodosPago = contenedor.querySelectorAll('.js-metodo-pago-evento');
    var mostrarPago = !esGratuito && !sinEvento && !sinTipo && valorEvento !== null && valorEvento > 0;
    var valorSiempreVisible = !!(campoValor && !campoValor.classList.contains('invisible') && !campoValor.hasAttribute('aria-hidden'));
    var formaPagoSiempreVisible = !!(bloqueFormaPago && bloqueFormaPago.classList.contains('col-md-6'));

    // En el formulario de registro los campos quedan visibles; solo se habilitan cuando aplica.
    if (campoValor) {
      if (valorSiempreVisible) {
        campoValor.style.display = '';
        campoValor.classList.remove('invisible');
        campoValor.setAttribute('aria-hidden', 'false');
      } else {
        alternarCampoReservado(campoValor, mostrarPago || esGratuito);
      }
    }

    if (bloqueFormaPago) {
      bloqueFormaPago.style.display = formaPagoSiempreVisible || mostrarPago ? '' : 'none';
    }

    if (bloquePagoLegacy) {
      bloquePagoLegacy.style.display = mostrarPago || esGratuito ? '' : 'none';
    }

    if (hiddenFormaPago) {
      hiddenFormaPago.disabled = !esGratuito;
    }

    if (hiddenValor) {
      hiddenValor.disabled = !esGratuito;
    }

    if (valorInput) {
      valorInput.disabled = !mostrarPago;
      valorInput.readOnly = false;
      valorInput.required = mostrarPago;
      valorInput.placeholder = esGratuito ? 'Gratuito' : 'Se completa según el tipo';

      if (mostrarPago && valorEvento > 0 && sugerirValor) {
        valorInput.value = valorEvento;
      } else if ((esGratuito || sinEvento || sinTipo) && sugerirValor) {
        valorInput.value = '';
      } else if (esGratuito) {
        valorInput.value = '';
      }
    }

    metodosPago.forEach(function (radio) {
      radio.disabled = !mostrarPago;
      radio.required = mostrarPago;
    });
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
        campoValor.style.display = esGratuito ? 'none' : '';
      }

      if (valorInput) {
        valorInput.disabled = esGratuito;
        valorInput.required = !esGratuito;
        if (esGratuito) {
          valorInput.removeAttribute('required');
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
      } else {
        input.value = '';
      }
    });

    lista.appendChild(nueva);
    actualizarBloqueValorCatalogo(contenedor);
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

    if (campoNumeracion.classList.contains('invisible') || campoNumeracion.hasAttribute('aria-hidden')) {
      alternarCampoReservado(campoNumeracion, requiere);
    } else {
      campoNumeracion.style.display = requiere ? '' : 'none';
    }

    if (inputNumeracion) {
      inputNumeracion.required = requiere;
      if (!requiere) {
        inputNumeracion.value = '';
      }
    }
  }

  function inicializarContenedorRegistro(contenedor) {
    var eventoSelect = contenedor.querySelector('select[name="evento_id"]');
    var tipoSelect = contenedor.querySelector('.js-tipo-entrada-evento');

    if (eventoSelect) {
      eventoSelect.addEventListener('change', function () {
        actualizarSelectTiposEntrada(contenedor);
        actualizarNumeracion(contenedor);
        actualizarBloquePagoEvento(contenedor, { sugerirValor: true });
      });
    }

    if (tipoSelect) {
      tipoSelect.addEventListener('change', function () {
        actualizarBloquePagoEvento(contenedor, { sugerirValor: true });
      });
    }

    actualizarSelectTiposEntrada(contenedor);
    actualizarNumeracion(contenedor);
    // En edición no pisa el valor guardado; en alta sugiere si el campo está vacío.
    var valorInput = contenedor.querySelector('.js-valor-evento');
    var sugerirAlInicio = !valorInput || !valorInput.value || parseFloat(valorInput.value) <= 0;
    actualizarBloquePagoEvento(contenedor, { sugerirValor: sugerirAlInicio });
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
    if (form.querySelector('select[name="evento_id"]') && (form.querySelector('.js-campo-valor-evento') || form.querySelector('.js-bloque-forma-pago-evento') || form.querySelector('.js-bloque-pago-evento') || form.querySelector('.js-campo-tipo-entrada-evento'))) {
      inicializarContenedorRegistro(form);
    }
  });

  document.querySelectorAll('#formAgregarEvento, .modal').forEach(function (contenedor) {
    if (contenedor.querySelector('.js-tipo-cobro-catalogo') || contenedor.querySelector('.js-tipos-entrada-lista')) {
      inicializarContenedorCatalogo(contenedor);
    }
  });
})();
