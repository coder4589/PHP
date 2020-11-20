<?php

$Sleep_Time = "3";

if (@$_POST["String"] == "Get_File_Content"){echo file_get_contents("Edit_File_Content.txt"); return;}

if (isset($_POST["String"])){

$File = "Edit_File_Content.txt";

$File_Write_Request = Check_File_Write_Queue($File);      //add a new write request to a file and wait here until older requests are finhished

$File_Content = file_get_contents($File);

$File_Content .= $_POST["String"];

sleep($Sleep_Time);       //sleep for x seconds

file_put_contents($File, $File_Content);

Check_File_Write_Queue($File_Write_Request, "Delete");
//if the above line is not used, the "register_shutdown_function()" used in the function will ensure that the "$File_Write_Request" will be removed when script exits!

echo "String Added";
return;
}

function Check_File_Write_Queue($File, $Option = "") {        //____________________________

$Files_Write_Queue = realpath("Edit_File_Content_Check_File_Write_Queue_List.txt");

    if ($Option == "Delete" || $Option == "delete"){
    $File_Content = file_get_contents($Files_Write_Queue);
    $File_Content = str_replace($File, "", $File_Content);
    file_put_contents($Files_Write_Queue, $File_Content);
    return;
    }

$File = realpath($File);

$Request_Id = microtime() . " " . bin2hex(random_bytes(10));

$Write_Request = $File . "<<<<" . $Request_Id . ">>>>\r\n";

file_put_contents($Files_Write_Queue, $Write_Request, FILE_APPEND);

    //to prevent errors\issues, "realpath()" should be used in "register_shutdown_function()"
    register_shutdown_function(function() use ($Files_Write_Queue,$Write_Request){
    $File_Content = file_get_contents($Files_Write_Queue);
    $File_Content = str_replace($Write_Request, "", $File_Content);
    file_put_contents($Files_Write_Queue, $File_Content);
    });

    while(1) {      //1=Infinite loop
    $File_Content = file_get_contents($Files_Write_Queue);
    preg_match('/' . preg_quote($File, '/') . '<<<<(.*?)>>>>/', $File_Content, $match);
    
    if (@$match[1] == $Request_Id){return $Write_Request;}
    }
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

    http.onreadystatechange = function(){
        if (this.readyState === 4) {     //"4", request finished and response is ready!
        document.getElementById("Ajax_Response").innerHTML += this.responseText + "<br>";
        }
    };

http.send('String=' + Option);
}

</script>
