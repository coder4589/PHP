<?php

$Write_Sleep = 10;      //seconds
$Read_Sleep = 2;        //seconds

$File = basename($_SERVER['SCRIPT_FILENAME'], '.php') . ".txt";

if (isset($_POST["Request"])){

    if($_POST["Request"] == "R"){sleep($Read_Sleep); echo file_get_contents($File);       //the read process takes some time to finish (Large Files)
    }elseif($_POST["Request"] == "LSH_R"){

        wait_Write_end_of($File, function($F_P){        //waits here if an older process is writing  the file until the file is unlocked!
        sleep($GLOBALS["Read_Sleep"]); return file_get_contents($F_P);                   //the read process takes some time to finish (Large Files)
        }, $File_Content, $flock_Message);

    echo ($flock_Message) ? $File_Content : "flock() failed";       //"wait_Write_end_of()" returns the same value as "$flock_Message"

    }else if ($_POST["Request"] == "W"){

        $File_Content = file_get_contents($File);
        $File_Content = str_replace("z", "y", $File_Content);

            //the below is basically what "file_put_contents()" does, except the "sleep()" part

        $File_Handle = fopen($File, 'w');

        sleep($Write_Sleep); fwrite($File_Handle, $File_Content . "z");     //the write process takes some time to finish (Large Files)

        fclose($File_Handle);

        echo "z to y (z Added)";

    }else{

        wait_Read_Write_end_of($File, function($F_P)use($Write_Sleep, &$W_M){    //waits here if an older process is reading or writing the file until the file is unlocked!

            //"&" allows this anonymous function to Get\Set "$W_M" variable from it's parent scope (In this case, the main script scope)!

        $File_Content = file_get_contents($F_P);
        $File_Content = str_replace("x", "w", $File_Content);

            //the below is basically what "file_put_contents()" does, except the "sleep()" part
 
        $File_Handle = fopen($F_P, 'w');

        sleep($Write_Sleep); fwrite($File_Handle, $File_Content . "x");      //the write process takes some time to finish (Large Files)
            
        fclose($File_Handle);
        
        $W_M = "x to w";        //the "$W_M" variable from this anonymous fucntion parent scope, the main script in this case, is also set to same value!

        return " (x Added)";      //this return value will be stored in "$Write_Message" var below

        }, $Write_Message, $flock_Message);

    echo ($flock_Message) ? $W_M . $Write_Message : "flock() failed";     //"wait_Read_Write_end_of()" returns the same value as "$flock_Message"
    }

return;
}

file_put_contents($File, "Oops_");

function wait_Write_end_of($File, $Do_This, &$Return_Var_1 = "", &$Return_Var_2 = ""){     //______________________________________________

    $File_Handle = fopen("$File", "r");
    if(flock($File_Handle, LOCK_SH)){       //waits here if an older process is already locking the file with "LOCK_EX" flag until the file is unlocked!
                                            //(Note) does not wait if the older proccess is locking the file with "LOCK_SH" flag!
    $Return_Message = 1;

    $Return_Var_1 = $Do_This($File);

    flock($File_Handle, LOCK_UN);       //unlock the file so new processes that want to lock the same file with "LOCK_EX" flag can continue execution!

    }else{$Return_Message = 0;}
    fclose($File_Handle);

$Return_Var_2 = $Return_Message;

return $Return_Message;
}

function wait_Read_Write_end_of($File, $Do_This, &$Return_Var_1 = "", &$Return_Var_2 = ""){      //__________________________________________

    $File_Handle = fopen($File, 'r');
    if (flock($File_Handle, LOCK_EX)) {     //waits here if an older process is already locking the file with "LOCK_EX" or "LOCK_SH" flags until the file is unlocked!
 
    $Return_Message = 1;
 
    $Return_Var_1 = $Do_This($File);

    flock($File_Handle, LOCK_UN);       //unlock the file so new processes that want to lock the same file with "LOCK_EX" or "LOCK_SH" flags can continue execution!

    }else{$Return_Message = 0;}
    fclose($File_Handle);

$Return_Var_2 = $Return_Message;

return $Return_Message;
}

?>

<!DOCTYPE html>

<div style="  float:left; ">
<input type="button" value="Write (Wait <?php echo $Write_Sleep ?> Seconds)"  onclick='Request("W", this, Call_Count++)'>
<br>
<div id="W"></div>
</div>

<div style="  float:left; margin-left: 20px;">
<input type="button" value="LOCK_EX Write (Wait <?php echo $Write_Sleep ?> Seconds)"  onclick='Request("LEX_W", this, Call_Count++)'>
<br>
<div id="LEX_W"></div>
</div>

<div style="  float:left; margin-left: 20px; ">
<input type="button" value="Read (Wait <?php echo $Read_Sleep ?> seconds)"  onclick='Request("R", this, Call_Count++)'>
<br>
<div id="R"></div>
</div>

<div style="  float:left; margin-left: 20px; ">
<input type="button" value="LOCK_SH Read (Wait <?php echo $Read_Sleep ?> seconds)"  onclick='Request("LSH_R", this, Call_Count++)'>
<br>
<div id="LSH_R"></div>
</div>

<br style=" clear: both;"><br><br>

<?php
echo $_SERVER['SCRIPT_FILENAME'] . "<br><br>";
echo __FILE__ . "<br><br>"; 

echo basename(__FILE__) . "<br><br>"; 
echo basename(__FILE__, '.php') . "<br><br>";

echo basename($_SERVER['SCRIPT_FILENAME']) . "<br><br>"; 
echo basename($_SERVER['SCRIPT_FILENAME'], '.php') . "<br><br>";
?>


<script>

Call_Count = 1;

function Request(Option, El, Call_Count){      //____________________________

    //El.disabled = true;

var http = new XMLHttpRequest();
http.open('POST', "");      //blank url (send to same page)

http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');     //necessary to send "Request" POST key below to php

    http.onreadystatechange = function(){
        if (this.readyState === 4) {     //"4", request finished and response is ready!

            if (Option === "LEX_W"){document.getElementById("LEX_W").innerHTML += this.responseText + " (Call " + Call_Count + ")<br>";
            }else if (Option === "W"){document.getElementById("W").innerHTML += this.responseText + " (Call " + Call_Count + ")<br>";
            }else if (Option === "LSH_R"){document.getElementById("LSH_R").innerHTML += '"' + this.responseText + '" (Call ' + Call_Count + ')<br>';
            }else{document.getElementById("R").innerHTML += '"' + this.responseText + '" (Call ' + Call_Count + ')<br>';}

        El.disabled = false;
        }
    };

http.send('Request=' + Option);
}

</script>
