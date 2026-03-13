<?php
echo "123456: " . password_hash("123456", PASSWORD_DEFAULT) . "\n";
echo "12345: " . password_hash("12345", PASSWORD_DEFAULT) . "\n";
echo "mega123: " . password_hash("mega123", PASSWORD_DEFAULT) . "\n";
?>