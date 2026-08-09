(function () {
  'use strict';

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

  function tipoEntradaEsGratis(contenedor) {
    var tipoSelect = contenedor.querySelector('.js-tipo-entrada-evento');
    if (!tipoSelect || !tipoSelect.value) {
      return false;
    }

    var opcionTipo = tipoSelect.options[tipoSelect.selectedIndex];
    return !!(opcionTipo && opcionTipo.getAttribute('data-es-gratis') === '1');
  }

  function sincronizarVisiblePublicoFila(fila) {
    var hidden = fila.querySelector('.js-visible-publico-valor');
    var check = fila.querySelector('.js-visible-publico-check');
    if (hidden && check) {
      hidden.value = check.checked ? '1' : '0';
    }
  }

  function sincronizarEsGratisFila(fila) {
    var hidden = fila.querySelector('.js-es-gratis-valor');
    var check = fila.querySelector('.js-es-gratis-check');
    var valorInput = fila.querySelector('.js-valor-tipo-entrada');

    if (hidden && check) {
      hidden.value = check.checked ? '1' : '0';
    }

    if (valorInput && check) {
      valorInput.disabled = check.checked;
      valorInput.required = !check.checked;
      if (check.checked) {
        valorInput.value = '0';
      }
    }
  }

  function enlazarVisiblePublicoFila(fila) {
    var check = fila.querySelector('.js-visible-publico-check');
    if (!check || check.dataset.boundVisiblePublico) {
      return;
    }

    check.dataset.boundVisiblePublico = '1';
    check.addEventListener('change', function () {
      sincronizarVisiblePublicoFila(fila);
    });
    sincronizarVisiblePublicoFila(fila);
  }

  function enlazarEsGratisFila(fila) {
    var check = fila.querySelector('.js-es-gratis-check');
    if (!check || check.dataset.boundEsGratis) {
      return;
    }

    check.dataset.boundEsGratis = '1';
    check.addEventListener('change', function () {
      sincronizarEsGratisFila(fila);
    });
    sincronizarEsGratisFila(fila);
  }

  function leerValorNumerico(input, fallback) {
    if (!input || input.value === '') {
      return fallback;
    }
    var n = parseFloat(input.value);
    return isNaN(n) ? fallback : n;
  }

  function puedeGestionarEstadoPago(contenedor) {
    var bloque = contenedor.querySelector('.js-form-registro-evento');
    var fuente = bloque || contenedor;

    if (fuente.getAttribute('data-solo-metodos-pago') === '1') {
      return false;
    }

    return fuente.getAttribute('data-puede-estado-pago') === '1';
  }

  function soloMetodosPago(contenedor) {
    var bloque = contenedor.querySelector('.js-form-registro-evento');
    var fuente = bloque || contenedor;
    return fuente.getAttribute('data-solo-metodos-pago') === '1';
  }

  function obtenerEstadoPagoSeleccionado(contenedor) {
    if (!puedeGestionarEstadoPago(contenedor)) {
      return 'por_cancelar';
    }

    var marcado = contenedor.querySelector('.js-estado-pago-evento:checked');
    return marcado ? marcado.value : 'por_cancelar';
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
        option.setAttribute('data-es-gratis', String(tipo.es_gratis ? 1 : 0));
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
    var puedeEstado = puedeGestionarEstadoPago(contenedor);
    var esSoloMetodos = soloMetodosPago(contenedor);
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
    var bloquePendienteTexto = contenedor.querySelector('.js-bloque-forma-pago-pendiente');
    var bloqueMetodos = contenedor.querySelector('.js-bloque-metodos-pago');
    var hiddenFormaPago = contenedor.querySelector('.js-forma-pago-gratuito');
    var hiddenFormaPagoPendiente = contenedor.querySelector('.js-forma-pago-pendiente');
    var hiddenValor = contenedor.querySelector('.js-valor-gratuito');
    var hiddenEstado = contenedor.querySelector('.js-estado-pago-gratuito');
    var hiddenEstadoOculto = contenedor.querySelector('.js-estado-pago-oculto');
    var hiddenEstadoCounter = contenedor.querySelector('.js-estado-pago-counter');
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

    var esGratisTipo = tipoListo && tipoEntradaEsGratis(contenedor);
    var mostrarPago = tipoListo && !esGratisTipo;
    var estadoActual = esGratisTipo ? 'pagado' : (tipoListo ? obtenerEstadoPagoSeleccionado(contenedor) : 'por_cancelar');
    var esPagado = estadoActual === 'pagado';
    var esPorCancelar = estadoActual === 'por_cancelar';
    var usarPendiente = mostrarPago && !esSoloMetodos && (esPorCancelar || !puedeEstado);
    var usarMetodos = mostrarPago && (esSoloMetodos || (esPagado && puedeEstado));

    setVisible(campoValor, true);
    pasosDespuesTipo.forEach(function (campo) {
      if (campo.classList.contains('js-valor-evento')) {
        campo.disabled = !tipoListo || esGratisTipo;
        campo.required = tipoListo && !esGratisTipo;
        campo.min = '0';
        campo.placeholder = esGratisTipo ? 'Gratuito' : '0.00';
        if (esGratisTipo) {
          campo.value = '0';
        }
        return;
      }

      campo.disabled = !tipoListo;
    });

    if (hiddenValor) {
      hiddenValor.disabled = !esGratisTipo;
      hiddenValor.value = '0';
    }

    if (valorInput) {
      if (esGratisTipo) {
        valorInput.removeAttribute('name');
      } else {
        valorInput.setAttribute('name', 'valor');
      }
    }

    setVisible(bloqueEstado, mostrarPago && puedeEstado);
    setVisible(bloqueFormaPago, mostrarPago);
    if (bloquePagoLegacy) {
      setVisible(bloquePagoLegacy, mostrarPago);
    }

    setVisible(bloquePendienteTexto, usarPendiente);
    setVisible(bloqueMetodos, usarMetodos);

    if (hiddenFormaPago) {
      hiddenFormaPago.disabled = !esGratisTipo;
    }

    if (hiddenFormaPagoPendiente) {
      hiddenFormaPagoPendiente.disabled = !usarPendiente;
    }

    if (hiddenEstado) {
      hiddenEstado.disabled = !esGratisTipo;
    }

    if (hiddenEstadoOculto) {
      hiddenEstadoOculto.disabled = esGratisTipo || puedeEstado || esSoloMetodos;
    }

    if (hiddenEstadoCounter) {
      hiddenEstadoCounter.disabled = !esSoloMetodos || esGratisTipo;
    }

    metodosPago.forEach(function (radio) {
      radio.disabled = !usarMetodos;
      radio.required = usarMetodos;
    });

    estadosPago.forEach(function (radio) {
      radio.disabled = !mostrarPago || !puedeEstado;
      radio.required = mostrarPago && puedeEstado;
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

  function actualizarFilasTipoEntrada(contenedor) {
    contenedor.querySelectorAll('.js-tipo-entrada-fila').forEach(function (fila) {
      sincronizarVisiblePublicoFila(fila);
      sincronizarEsGratisFila(fila);
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
      if (input.classList.contains('js-valor-tipo-entrada')) {
        input.value = '0';
        input.min = '0';
        input.disabled = false;
        input.required = true;
      } else if (input.classList.contains('js-visible-publico-valor')) {
        input.value = '1';
      } else if (input.classList.contains('js-es-gratis-valor')) {
        input.value = '0';
      } else if (input.classList.contains('js-visible-publico-check')) {
        input.checked = true;
        input.removeAttribute('data-bound-visible-publico');
        input.removeAttribute('id');
      } else if (input.classList.contains('js-es-gratis-check')) {
        input.checked = false;
        input.removeAttribute('data-bound-es-gratis');
        input.removeAttribute('id');
      } else if (input.type === 'text') {
        input.value = '';
      }
    });

    nueva.querySelectorAll('label[for]').forEach(function (label) {
      label.removeAttribute('for');
    });

    lista.appendChild(nueva);
    enlazarVisiblePublicoFila(nueva);
    enlazarEsGratisFila(nueva);
    actualizarFilasTipoEntrada(contenedor);
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

    contenedor.querySelectorAll('.js-estado-pago-evento').forEach(function (radio) {
      if (radio.dataset.boundEstadoPagoEvento) {
        return;
      }
      radio.dataset.boundEstadoPagoEvento = '1';
      radio.addEventListener('change', function () {
        actualizarBloquePagoEvento(contenedor, { sugerirValor: false });
      });
    });

    var sugerirAlInicio = !valorInput || valorInput.value === '';
    refrescarFormularioRegistro(contenedor, { sugerirValor: sugerirAlInicio });
  }

  function inicializarContenedorCatalogo(contenedor) {
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

    contenedor.querySelectorAll('.js-tipo-entrada-fila').forEach(function (fila) {
      enlazarVisiblePublicoFila(fila);
      enlazarEsGratisFila(fila);
    });
    actualizarFilasTipoEntrada(contenedor);
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
    if (contenedor.querySelector('.js-tipos-entrada-lista')) {
      inicializarContenedorCatalogo(contenedor);
    }
  });
})();
