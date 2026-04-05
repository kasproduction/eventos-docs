<?php

$id = $GLOBALS['pantalla_id_param'];
$db = DB::get();
$db->prepare("DELETE FROM agenda_pantallas WHERE id = ?")
   ->execute([$id]);

Response::json(['ok' => true]);
