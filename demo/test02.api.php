<?php
// test02.api.php
assert($_POST['name'] == 'test02');
var_dump($_FILES);

rename($_FILES['picture']['tmp_name'], 'picture.png');
