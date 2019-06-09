<?php

$User_String = 'A AA AAA Test B BB BBB';

$M_User_String = preg_replace_callback('/A|B/', function ($match) {

if ($match[0] === 'A')
return 'B';
else if ($match[0] === 'B')
return 'A';

}, $User_String);

echo '
'.$User_String.' (Original Text) <br>
'.$M_User_String.' (A and B swapped)<br>
<br>
';

//----------------------- Another Example ------------------------

$User_String = 'A _A_ Test _B_ B';

$M_User_String = preg_replace_callback('/(?<=_)A|B(?=_)/', function ($match) {

if ($match[0] === 'A')
return 'B';
else if ($match[0] === 'B')
return 'A';

}, $User_String);

echo '
'.$User_String.' (Original Text) <br>
'.$M_User_String.' (A and B enclosed by _ _ swapped)<br>
<br>
';

//----------------------- Another Example ------------------------

$User_String = 'A A1 Test B2 B';

$M_User_String = preg_replace_callback('/(A|B)(\d)/', function ($match) {

if ($match[1] === 'A')
return $match[2] . 'B';
else if ($match[1] === 'B')
return $match[2] . 'A';

}, $User_String);

echo '
'.$User_String.' (Original Text) <br>
'.$M_User_String.' (A and B with a digit to their right are swapped with the digit to their left)<br>
<br>
';

?>




