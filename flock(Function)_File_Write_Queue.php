<?php

$Sleep_Time = "3";

if (@$_POST["String"] == "Get_File_Content"){echo file_get_contents("Edit_File_Content.txt"); return;}

if (isset($_POST["String"])){

    $File = "Edit_File_Content.txt";
    $File_Handle = fopen("$File", "r");
    if(flock($File_Handle, LOCK_EX)){       //on success, this script execution waits here until older scripts in queue unlock the file

    $File_Content = file_get_contents($File);

    $File_Content .= $_POST["String"];

    sleep($Sleep_Time);       //sleep for x seconds

    file_put_contents($File, $File_Content);

    flock($File_Handle, LOCK_UN);       //unlock the file so new scripts in queue can continue execution

    echo "flock() success [String Added]";
    
    }else{echo "flock() Failed";}
    fclose($File_Handle);

return;
}

?>

Each request takes <?php echo $Sleep_Time ?> seconds to finish!
<br><br>
<input type="button"  value="Add A" onclick='Add_String("A")'>
<input type="button"  value="Add B" onclick='Add_String("B")'>
<input type="button"  value="Get File Content" onclick='Add_String("Get_File_Content")'>
<br><br>
<div id="Ajax_Response">Ajax Response:<br><br></div>

<script>

function Add_String(Option){      //____________________________

var http = new XMLHttpRequest();
http.open('POST', "");      //blank url (send to same page)

http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');     //necessary to send "String" POST key below to php

http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');     //necessary to send "String" POST key below to php

    http.onreadystatechange = function(){
        if (this.readyState === 4) {     //"4", request finished and response is ready!
        document.getElementById("Ajax_Response").innerHTML += this.responseText + "<br>";
        }
    };

http.send('String=' + Option);
}

</script>
