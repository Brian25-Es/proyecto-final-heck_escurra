<?php
// genera_hash.php
// Cambiá la contraseña en $plaintext si querés otra
$plaintext = 'G7#k9vLq2!Rd4sWz';

// Genera el hash con el algoritmo por defecto de PHP (bcrypt/argon2 según versión)
$hash = password_hash($plaintext, PASSWORD_DEFAULT);

// Muestra el resultado en pantalla
echo "Contraseña: $plaintext\n";
echo "Hash (para guardar en la BD):\n";
echo $hash . "\n";
?>