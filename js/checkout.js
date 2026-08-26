(function () {
  'use strict';

  var form = document.querySelector('.js-checkout-form');
  if (!form) {
    return;
  }

  var evento = form.querySelector('#evento_id');
  var numeracion = form.querySelector('#numeracion');

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

      if (evento.value && numeracion) {
        numeracion.value = '';
        numeracion.focus();
      }
    });
  }

  sincronizarNumeracion();

  if (evento && evento.value && numeracion && !numeracion.disabled) {
    numeracion.focus();
    numeracion.select();
  }
})();
