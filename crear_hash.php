<?php
// Define la contraseña que quieres usar
$contrasenaPlana = 'tu_contraseña_aqui';

// Genera el hash seguro
$hash = password_hash($contrasenaPlana, PASSWORD_DEFAULT);

// Muestra el hash en pantalla
echo "Copia este hash y pégalo en la columna 'contrasena' de tu usuario en phpMyAdmin:";
echo "<br><br>";
echo "<strong>" . $hash . "</strong>";
?>