<?php

require_once 'includes/auth.php';

cerrarSesion();

header('Location: index.php');
exit;
