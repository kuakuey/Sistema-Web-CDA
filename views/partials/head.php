<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>
(function () {
  try {
    if (localStorage.getItem('cda-theme') === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
      document.documentElement.setAttribute('data-bs-theme', 'dark');
    }
  } catch (e) {}
})();
</script>
<title><?= htmlspecialchars($tituloPagina ?? 'App') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="css/theme.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
