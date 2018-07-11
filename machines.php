<?php
/*
 * Coded by Raghu Nandan Immaneni
 * Ammended and debugged by Khai Nguyen Summer 2015
 * PHP file that dynamically collects & process the RAID array status &
 * smartctl output of all servers & desktops.
 * EDITED ON: 10/24/16 BY XAVIER MARTINEZ (Added recognition of entropy1's Virtual Disk)
 */

/* Please don't edit this file through "nano" or "vim" due to format error.
 *      Instead use VS code or other code editor for format consistency.
 * TESTING
 */
?>

<html>
    <head>
        <title>Sorin Lab: Machines</title>
        <link rel="stylesheet" href="labstyle.css" type="text/css"/>
        <script>
            window.onload = function findCriticalElements() 
            {
                var criticalElements = document.getElementsByClassName('critical');
                if (criticalElements.length === 1) 
                {
                    document.getElementById("criticalMessage").innerHTML = " &#9733; 1 Action Item &#9733;";
                    document.getElementById("criticalMessage").style.display = "block";
                }
                if (criticalElements.length > 1) 
                {
                    document.getElementById("criticalMessage").innerHTML = "&#9733; "+criticalElements.length+" Action Items &#9733;";
                    document.getElementById("criticalMessage").style.display = "block";
                }
                //Code for extracting file contents
                var slideSource = document.getElementById("slideSource");
                var displayHeader = document.getElementById("fileDisplayHeader");
                var dialogOverlay = document.getElementById("dialog_overlay");
                //Attach the click events to all the divs
                var fileHandlers = document.getElementsByClassName("handle");
                for(var i = 0; i < fileHandlers.length; i++)
                {
                    fileHandlers[i].addEventListener('click',(function(i) 
                    {
                        return function() 
                        {
                            var filename = fileHandlers[i].getAttribute("name");
                            getFileContents(filename);
                        };
                    })(i), false);
                }
                //Call back function that displays the content of the files on
                function getFileContents(filename) 
                {
                    displayHeader.innerHTML = filename.slice(0,-4);
                    dialogOverlay.style.display ='block';
                    slideSource.style.display = 'block';
                    //Load the contents of the file using AJAX
                    var xmlhttp;
                    // code for IE7+, Firefox, Chrome, Opera, Safari
                    if (window.XMLHttpRequest)
                    {
                        xmlhttp = new XMLHttpRequest();
                    }
                    // code for IE6, IE5
                    else
                    {
                        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    xmlhttp.onreadystatechange = function() 
                    {
                        //If the AJAX request is succesful display the file contents
                        if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
                        {
                            document.getElementById("slideSource").scrollTop = 0;
                            document.getElementById("slideSource").scrolLeft = 0;
                            document.getElementById("fileContent").innerHTML="<pre>"+xmlhttp.responseText+"</pre>";
                            document.getElementById("slideSource").focus();
                        }
                        //If the file in not found, display suitable error message
                        else if (xmlhttp.readyState == 4 && xmlhttp.status == 404)
                        {
                            document.getElementById("slideSource").scrollTop= 0;
                            document.getElementById("slideSource").scrollLeft= 0;
                            document.getElementById("fileContent").innerHTML="File Not Found";
                        }
                        else
                        {
                            document.getElementById("fileContent").innerHTML="Error Occured, Please contact Administrator";
                        }
                    }
                    xmlhttp.open("GET",filename,true);
                    xmlhttp.send();
                }//End of getFileContents() method

                //When we click on the dialog overlay div disappears
                document.getElementById("dialog_overlay").onclick = function(){
                    dialogOverlay.style.display = 'none';
                    slideSource.style.display = 'none';
                }

                //On press of escape key close the div
                document.onkeydown = function(evt) {
                    evt = evt || window.event;
                    if (evt.keyCode === 27) {
                        dialogOverlay.style.display = 'none';
                        slideSource.style.display = 'none';
                    }
                };
            } //End Window Load Event
        </script>
    </head>

    <body>
        <div id="mainDiv">
            <div id="header">
                <h2> SORIN LAB RAID INFO </h2>
                <ul id="mainNav">
                    <li><a href="http://spot.cnsm.csulb.edu/ganglia/">Ganglia</a></li>
                    <li><a href="http://spot.cnsm.csulb.edu/status.html">Spot-Status</a></li>
                    <li><a href="http://folding.cnsm.csulb.edu/wiki/index.php/SysAdmin_To_Do_List">To-Do</a></li>
                    <li><a href="http://folding.cnsm.csulb.edu/wiki">Wiki</a></li>
                    <li><a href="http://folding.cnsm.csulb.edu/wiki/index.php/Machine_Monitoring_Page">Monitor-Info</a></li>
                </ul>
            </div> <!-- End of div header -->
            <!-- Critical elements alert -->
            <span id="criticalMessage" style="display:none;"> 
            </span>
            <div id="servers">
                <h2> SERVERS </h2>
                <?php
                //Array of file names that store server info
                // To add a new server add the new file names here
                $filenames = array('banana-DataRaid.txt','banana-OSRaid.txt','storage1-Data.txt','storage1-OS.txt', 'entropy1.txt', 'entropy1-vdisk.txt', 'folding1.txt','folding2.txt');
                //Process each file in the array one by one
                foreach ($filenames as $name) 
                {
                    $MNAME = "NOTBANANA";
                    // Initially set the smartEnabled to false.
                    // This value remains zero for the machines on which smartctl is not enabled
                    $smartEnabledDisks = 0;
                    //Create a new div
                    echo "<div class=\"machine\">";
                    //Set div headers and attach smartctl data (if smart support is enabled)
                    if (strpos($name,"banana-OS") !== FALSE) 
                    {
                        echo "<h3 class=\"machineName\">banana OS RAID</h3>";
                        $smartfile1 = "smartctl/banana-sdc.txt"; $smartdisk1 = "/dev/sdc";
                        $smartfile2 = "smartctl/banana-sdd.txt"; $smartdisk2 = "/dev/sdd";
                        $smartEnabledDisks = 2;
                        $MNAME = "banana";
                    }
                    if (strpos($name,"banana-Data") !== FALSE) 
                    {
                        echo "<h3 class=\"machineName\">banana Data RAID</h3>";
                        $smartfile1 = "smartctl/banana-sda.txt"; $smartdisk1 = "/dev/sda";
                        $smartfile2 = "smartctl/banana-sdb.txt"; $smartdisk2 = "/dev/sdb";
                        $smartEnabledDisks = 2;
                        $MNAME = "banana";
                    }
                    if (strpos($name,"entropy1-vdisk") !== FALSE) 
                    {
                        echo "<h3 class=\"machineName\">entropy1 OS RAID</h3>";
                        $smartfile1 = "smartctl/entropy1-sg6.txt"; $smartdisk1 = "/dev/sg6";
                        $smartfile2 = "smartctl/entropy1-sg7.txt"; $smartdisk2 = "/dev/sg7";
                        $smartEnabledDisks = 2;
                        $MNAME = "entropy1";
                    }
                    if (strpos($name,"entropy1.") !== FALSE) 
                    {
                        echo "<h3 class=\"machineName\">entropy1 Data RAID</h3>";
                        $smartfile1 = "smartctl/entropy1-sde.txt"; $smartdisk1 = "/dev/sde";
                        $smartfile2 = "smartctl/entropy1-sdf.txt"; $smartdisk2 = "/dev/sdf";
                        $smartEnabledDisks = 2;
                        $MNAME = "entropy1";
                    }
                    if (strpos($name,"folding1") !== FALSE)
                    {
                        echo "<h3 class=\"machineName\">folding1</h3>";
                        $smartfile1 = "smartctl/folding1-sda.txt"; $smartdisk1 = "/dev/sda";
                        $smartfile2 = "smartctl/folding1-sdb.txt"; $smartdisk2 = "/dev/sdb";
                        $smartfile3 = "smartctl/folding1-sdc.txt"; $smartdisk3 = "/dev/sdc";
                        $smartfile4 = "smartctl/folding1-sdd.txt"; $smartdisk4 = "/dev/sdd";
                        $smartEnabledDisks = 4;
                    }
                    if (strpos($name,"folding2") !== FALSE) 
                    {
                        echo "<h3 class=\"machineName\">folding2</h3>";
                        $smartfile1 = "smartctl/folding2-sda.txt"; $smartdisk1 = "/dev/sda";
                        $smartfile2 = "smartctl/folding2-sdb.txt"; $smartdisk2 = "/dev/sdb";
                        $smartfile3 = "smartctl/folding2-sdc.txt"; $smartdisk3 = "/dev/sdc";
                        $smartfile4 = "smartctl/folding2-sdd.txt"; $smartdisk4 = "/dev/sdd";
                        $smartEnabledDisks = 4;
                    } 
                    if(strpos($name,"storage1-OS") !== FALSE) 
                    {
                        echo "<h3 class=\"machineName\">storage1 OS RAID</h3>";
                        $smartfile1 = "smartctl/storage1-sdg.txt"; $smartdisk1 = "/dev/sdg";
                        $smartfile2 = "smartctl/storage1-sdh.txt"; $smartdisk2 = "/dev/sdh";
                        $smartEnabledDisks = 2;
                    }
                    if (strpos($name,"storage1-Data") !== FALSE) 
                    {
                        echo "<h3 class=\"machineName\">storage1 Data RAID</h3>";
                        $smartfile1 = "smartctl/storage1-sde.txt"; $smartdisk1 = "/dev/sde";
                        $smartfile2 = "smartctl/storage1-sdf.txt"; $smartdisk2 = "/dev/sdf";
                        $smartEnabledDisks = 2;
                    }
                    //Generate list of required output
                    echo "<ul class=\"machineInfo\">";
                    //Check if the file exists on the server
                    if (file_exists($name)) 
                    {
                        $fh = file($name, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
                        if ($name === 'perutz.txt' || $name === 'folding1-raidstat.txt' || $name === 'folding2-raidstat.txt') 
                        {
                            $lastModified = filemtime($name);
                            $now = time();
                            $timediff = $now - $lastModified;
                            if ($timediff < 129600) 
                            {
                                echo "<li class=\"active\"> Last Updated : ".date("F d Y H:i:s",filemtime($name))."</li>";
                            }
                            else 
                            {
                                echo "<li class=\"error\"> Last Updated : ".date("F d Y H:i:s",filemtime($name))."</li>";
                            }
                        }
                        foreach ($fh as $line) 
                        {
                            //Make the md device as header and attach a file handle to it
                            if (strpos($line,"/dev/md") !== FALSE )
                            {
                                echo "<h4 class=\"handle\" name=".$name.">".$line."</h4>";
                                continue;
                            }
                            // Special case for entropy1 Virtual Disk
                            if (strpos($line,"Virtual Disks") !== FALSE)
                            {
                                echo "<h4 class=\"handle\" name=".$name.">Virtual Disk</h4>";
                                continue;
                            }
                            if(strpos($line,"Update Time") !==FALSE || strpos($line,"Generated On") !==FALSE) 
                            {
                                //Extract the update time from the line
                                if(strpos($line,"Update Time") !==FALSE)
                                {
                                    $date_string = preg_replace('/Update Time : /', '',$line);
                                }
                                if(strpos($line,"Generated On") !==FALSE)
                                {
                                    $date_string = preg_replace('/Generated On : /', '',$line);
                                }
                                // Convert the string to time in seconds
                                $updatetime = strtotime($date_string);
                                //Calculate the present time
                                $now = time();
                                //Compute the difference
                                $timediff = $now - $updatetime;
                                //If the difference is more than 36 hr (1 week = 604800 sec)
                                if($timediff > 129600) 
                                {
                                    if(strpos($line,"Update Time") !==FALSE)
                                    {
                                        echo "<li class=\"warning\">".$line."</li>";
                                    }
                                    if(strpos($line,"Generated On") !==FALSE)
                                    {
                                        echo "<li class=\"error\">".$timediff."</li>";
                                    }
                                    //This section is used to sent out an alert if any system is unpingable.
                                    if (strpos($MNAME,"NOTBANANA") !== FALSE) 
                                    {
                                        $length = strlen($name);
                                        $nameTemp = substr($name, 0, $length - 4);
                                        $pingReturn = 0;
                                        $pingOutput = exec("ping $nameTemp -c 1 -W 1", $pingTemp, $pingReturn);
                                        if ($pingReturn != 0) 
                                        {
                                            echo "<li style=\"font-size: 20pt;\" class=\"error\"> SYSTEM IS UNPINGABLE!!! </li>";
                                        }
                                    } 
                                    else 
                                    {
                                        $pingReturn = 0;
                                        $pingOutput = exec("ping $MNAME -c 1 -W 1", $pingTemp, $pingReturn);
                                        if ($pingReturn != 0) 
                                        {
                                            echo "<li style=\"font-size: 20pt;\" class=\"error\"> SYSTEM IS UNPINGABLE!!! </li>";
                                        }
                                    }
                                } 
                                else 
                                {
                                    echo "<li class=\"active\">".$line."</li>";
                                }
                                continue;
                            }
                            if (strpos($line,"Failed Devices")!==FALSE) 
                            {
                                $failed_devices = intval(preg_replace('/Failed Devices : /', '',$line));
                                if ($failed_devices > 0) 
                                {
                                    echo "<li class=\"error\">".$line."</li>";
                                }
                                else 
                                {
                                    echo "<li>".$line."</li>";
                                }
                                continue;
                            }
                            //Code for checking the state
                            if(strpos($line,"Major")!==FALSE)
                            {
                                echo "<li><pre>".$line."</pre></li>";
                                continue;
                            }
                            // Note: we prevent output for entropy1's Virtual Disk. It uses omreport.
                            if(strpos($line,"dev/sd")!==FALSE and $name !== "entropy1-vdisk.txt")
                            {
                                if(strpos($line,"active sync")!==false)
                                {
                                    echo "<li><pre>".$line."</pre></li>";
                                }
                                else
                                {
                                    echo "<li class=\"error\"><pre>".$line."</pre></li>";
                                }
                                continue;
                            }
                            //Code for identifying the state of RAID Array
                            // Note: we prevent output for entropy1's Virtual Disk. It uses omreport.
                            if(strpos($line,"State") !==FALSE and $name !== "entropy1-vdisk.txt")
                            {
                                //check if the state is clean or active else display an error
                                if(strpos($line,"clean")!==FALSE || strpos($line,"active")!==FALSE || strpos($line,"Online")!==FALSE) 
                                {
                                    //Further check to see if the line contains inactive, degraded, idle
                                    if(strpos($line,"inactive")!==FALSE || strpos($line,"degraded")!==FALSE || strpos($line,"idle")!==FALSE) 
                                    {
                                        echo "<li class=\"error\">".$line."</li>";
                                    }
                                    else
                                    {
                                        echo "<li class=\"active\">".$line."</li>";
                                    }
                                }
                                else 
                                {
                                    echo "<li class=\"error\">".$line."</li>";
                                }
                                continue;
                            }
                            //Code for others
                            if  (strpos($line,"Array Size")!==FALSE || strpos($line,"Active Devices")!==FALSE || strpos($line,"Working Devices")!==FALSE || strpos($line,"Controller")!==FALSE || strpos($line,"Disk Space")!==FALSE) 
                            {
                                echo "<li>".$line."</li>";
                            }
                            // Special case for entropy1's Virtual Disk. Reports size of array (not "Stripe element size")
                            if (strpos($line, "Size")!==FALSE and strpos($line, "Stripe")===FALSE and $name === "entropy1-vdisk.txt")
                            {
                                echo "<li>".$line."</li>";
                            }
                            // Special case for entropy1's Virtual Disk. Reports the state of the RAID array (prevents "T10 Protection Information Status" from triggering error).
                            if (strpos($line,"Status")!==FALSE and strpos($line, "Protection")===FALSE)
                            {
                                if(strpos($line,"Ok")!==FALSE)
                                {
                                    echo "<li class=\"active\">Status : Ok</li>";
                                }
                                else 
                                {
                                    echo "<li class=\"critical\">"."Possible RAID Degredation"."</li>";
                                }
                            }
                        } // End of file processing
                    } //End of file_exists
                    //If the specified file doesn't exist, display error
                    else 
                    {
                        echo "<li class=\"error\"> File Not Found </li>";
                    }
                    echo "<br/>";
                    // Parse the smartctl output for each machine and then print relevant details.
                    // For the machines on which smart support is not enabled the value remains zero.
                    // Else process the output based on the number of disks available on the machine.
                    if ($smartEnabledDisks > 0) 
                    {
                        for ($j = 1; $j <= $smartEnabledDisks; $j++) 
                        {
                            $shortOffline = 0;
                            $extendedOffline = 0;
                            $avgCount = 0;
                            $totalLifeTime = 0;
                            $errorsLogged = true;
                            $errorCount = 0;
                            $smartSupport = true;
                            //Extract the file and diskname from variables declared/defined above
                            $filename = ${'smartfile'.$j};
                            $diskname = ${'smartdisk'.$j};
                            echo "<h4 class=\"handle\" name=".$filename."> Hard Disk [".$diskname."]</h4>";
                            //Extract the contents of the file
                            $fh=file($filename,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
                            //Process each line in the file
                            foreach($fh as $line) 
                            {
                                //Check if smart support is configured on the disk
                                if(strpos($line,"SMART support is:")!==FALSE)
                                {
                                    if(strpos($line,"Unavailable")!==FALSE)
                                    {
                                        echo "<li class=\"critical\">"."$line"."</li>";
                                        $smartSupport=false;
                                        break;
                                    }
                                }
                                // Added "Errors Corrected" in the case that there is no errors logged
                                if (strpos($line,"No Errors Logged")!==FALSE || strpos($line, "Errors Corrected")!==FALSE) 
                                {
                                    $errorsLogged=false;
                                }
                                //If there are any errors logged, count the number of errors
                                if(strpos($line,"Error")!==FALSE) 
                                {
                                    if(strpos($line,"occurred at disk power-on lifetime")!==FALSE)
                                    {
                                        $errorCount++;
                                    }
                                }
                                if(strpos($line,"Local Time is")!==FALSE)
                                {
                                    $date_string = preg_replace('/Local Time is: /', '',$line);
                                    // Convert the string to time in seconds
                                    $updatetime = strtotime($date_string);
                                    //Caliculate the present time
                                    $now = time();
                                    //Compute the difference
                                    $timediff = $now - $updatetime;
                                    //If the difference is more than one day
                                    if($timediff > 86400)
                                    {
                                        echo "<li class=\"error\">".$line."</li>";
                                    }
                                    else 
                                    {
                                        echo "<li class=\"active\">".$line."</li>";
                                    }
                                    continue;
                                }
                                // Added the "Health Status" & "OK" in the if statement for entropy1's sde SMARTCTL
                                if(strpos($line,"overall-health")!==FALSE || strpos($line, "Health Status:")!==FALSE)
                                {
                                    if(strpos($line,"PASSED") || strpos($line, "OK"))
                                    {
                                        echo "<li class=\"active\">".$line."</li>";
                                    }
                                    else
                                    {
                                        echo "<li class=\"critical\">".$line."</li>";
                                    }
                                    continue;
                                }
                                /*
                                * The type of the attribute: old_age vs pre_fail
                                * Either “Pre-fail” for attributes indicate impending failure
                                        “Old_age” for attributes that just indicate wear and tear.
                                * Note that one and the same attribute can be classified as “Pre-fail” by one 
                                        manufacturer or for one model and as “Old_age” by another or for another model.
                                * Each Attribute also has a Threshold value (whose range is 0 to 255) which is printed 
                                        under the heading "THRESH".
                                * If the Normalized value is less than or equal to the Threshold value, then the 
                                        attribute is said to have failed.
                                * If the Attribute is a pre-failure Attribute, then disk failure is imminent.
                                */
                                //If a pre-fail attribute contains "FAILING_NOW", mark the disk status as critical
                                if(strpos($line,"Pre-fail")!==FALSE)
                                {
                                    if(strpos($line,"FAILING_NOW") || strpos($line,"failing_now"))
                                    {
                                        echo "<li class=\"critical\">"."Pre-fail attribute failed"."</li>";
                                    }
                                    continue;
                                }
                                //If an old-age attribute is failing mark it as error
                                if(strpos($line,"Old_age")!==FALSE)
                                {
                                    if(strpos($line,"FAILING_NOW") || strpos($line,"failing_now"))
                                    {
                                        echo "<li class=\"error\">"."Old_age attribute failed"."</li>";
                                    }
                                    continue;
                                }
                                //Now compute the average life time of the disk
                                //By considering latest 4 short offline & 4 extended offline lifetime values and computing an average
                                if (strpos($line,"Short offline")!==FALSE && strpos($line,"Completed without error")!==FALSE && $shortOffline < 4) 
                                {
                                    $shortOffline++;
                                    if (preg_match_all('!\d+!', $line, $matches)) 
                                    {
                                        if(isset($matches[0][2]))
                                        {
                                            if($matches[0][2] > 1000)
                                            {
                                                $totalLifeTime+=(int)$matches[0][2];
                                                $avgCount++;
                                            }
                                        }
                                    }
                                    continue;
                                }
                                if (strpos($line,"Extended offline")!==FALSE && strpos($line,"Completed without error")!==FALSE && $extendedOffline < 4) 
                                {
                                    $extendedOffline++;
                                    if (preg_match_all('!\d+!', $line, $matches)) 
                                    {
                                        if(isset($matches[0][2]))
                                        {
                                            if($matches[0][2] > 1000)
                                            {
                                                $totalLifeTime+=(int)$matches[0][2];
                                                $avgCount++;
                                            }
                                        }
                                    }
                                    continue;
                                }
                            } //End of Processing each line in file
                            if($smartSupport)
                            {
                                //Compute the disk life time in days
                                //Checking for zero inorder to avoid divide by zero exception
                                if($avgCount!=0)
                                {
                                    $diskLifeTime = (int)(($totalLifeTime/$avgCount)/(24*30));
                                }
                                else
                                {
                                    $diskLifeTime = 0;
                                }
                                echo "<li>Disk Age : ".$diskLifeTime." Months </li>";
                                if($errorsLogged)
                                {
                                    echo "<li class=\"critical\">".$errorCount." Error(s) Logged </li>";
                                }
                                //Add line break after the output for all disks except for the last disk.
                                if($j!=$smartEnabledDisks)
                                {
                                    echo "<br/>";
                                }
                            }
                        } //End of for-loop with $smartEnabledDisks
                        echo "</ul>"; //End of machine Info
                    }//End of If smartEnabledDisks
                    echo "</div>"; //End of div
                }//End of Loop
                //Starting Loop for displaying all of the machines onto the machine monitoring page in banana************
                ?>
            </div> <!-- End of div for Server -->
            <div id="desktops">
                <h2> DESKTOPS </h2>
                <?php
                    $offline_desktops = array(2,5);
                    for($i=1;$i<=13;$i++)
                    {
                        //Generate the file name
                        $filename = "sorin".$i.".txt";
                        //Create the div for each machine
                        echo "<div class=\"machine\">";
                        echo "<h3 class=\"machineName\">"."sorin".$i."</h3>";
                        echo "<ul class=\"machineInfo\">";
                        //Check if the file exists
                        if (in_array($i, offline_desktops)) {
                            echo "HOSTNAME NOT CURRENTLY IN USE";
                            continue;
                        }
                        if(file_exists($filename))
                        {
                            $fh=file($filename,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
                            foreach ($fh as $line)
                            {
                                if(strpos($line,"Array Size")!==FALSE)
                                {
                                    echo "<li>".$line."</li>";
                                    continue;
                                }
                                //Print the device name
                                if(strpos($line,"/dev/md")!== FALSE)
                                {
                                    echo "<h4 class=\"handle\" name=".$filename.">".$line."</h4>";
                                    continue;
                                }
                                //Update Time and Generated On cannot be more than one day
                                if(strpos($line,"Update Time") !==FALSE || strpos($line,"Generated On") !==FALSE)
                                {
                                    //Extract the update time from the line
                                    if(strpos($line,"Update Time") !==FALSE)
                                    {
                                        $date_string = preg_replace('/Update Time : /', '',$line);
                                    }
                                    if(strpos($line,"Generated On") !==FALSE)
                                    {
                                        $date_string = preg_replace('/Generated On : /', '',$line);
                                    }
                                    // Convert the string to time in seconds
                                    $updatetime = strtotime($date_string);
                                    //Caliculate the present time
                                    $now = time();
                                    //Compute the difference
                                    $timediff = $now - $updatetime;
                                    //If the difference is more than 36 hrs
                                    if($timediff > 129600)
                                    {
                                        if(strpos($line,"Update Time") !==FALSE)
                                        {
                                            echo "<li class=\"warning\">".$line."</li>";
                                        }
                                        if(strpos($line,"Generated On") !==FALSE)
                                        {
                                            echo "<li class=\"error\">".$line."</li>";
                                        }
                                        //This section is used to sent out an alert if any system is unpingable.
                                        $length = strlen($filename);
                                        $nameTemp = substr($filename, 0, $length - 4);
                                        $pingReturn = 0;
                                        $pingOutput = exec("ping $nameTemp -c 2 -W 1", $pingTemp, $pingReturn);
                                        if ($pingReturn != 0) 
                                        {
                                            // echo "<script> alert('" . $nameTemp . " is Unpingable!!!'); </script>";
                                            echo "<li style=\"font-size: 20pt;\" class=\"error\"> SYSTEM IS UNPINGABLE!!! </li>";
                                        }
                                    }
                                    else
                                    {
                                        echo "<li class=\"active\">".$line."</li>";
                                    }
                                    continue;
                                }
                                //Code for identifying the state of RAID Array
                                if(strpos($line,"State :") !==FALSE)
                                {
                                    //check if the state is clean or active else display an error
                                    if(strpos($line,"clean")!==FALSE || strpos($line,"active")!==FALSE)
                                    {
                                        //Further check to see if the line contains inactive, degraded, idle
                                        if(strpos($line,"inactive")!==FALSE || strpos($line,"degraded")!==FALSE || strpos($line,"idle")!==FALSE)
                                        {
                                            echo "<li class=\"error\">".$line."</li>";
                                        }
                                        else
                                        {
                                            echo "<li class=\"active\">".$line."</li>";
                                        }
                                    }
                                    else
                                    {
                                        echo "<li class=\"error\">".$line."</li>";
                                    }
                                    continue;
                                }
                                if(strpos($line,"Active Devices")!==FALSE || strpos($line,"Working Devices")!==FALSE)
                                {
                                    echo "<li>".$line."</li>";
                                }
                                if(strpos($line,"Failed Devices")!==FALSE)
                                {
                                    $failed_devices = intval(preg_replace('/Failed Devices : /', '',$line));
                                    if($failed_devices > 0)
                                    {
                                        echo "<li class=\"error\">".$line."</li>";
                                    }
                                    else
                                    {
                                        echo "<li>".$line."</li>";
                                    }
                                    continue;
                                }
                                if(strpos($line,"Major")!==FALSE)
                                {
                                    echo "<li><pre>".$line."</pre></li>";
                                    continue;
                                } 
                                //Add dev/sdXX here for new drives. So the page lines up all harddrives
                                if  (   strpos($line,"dev/sda1")!==FALSE ||
                                        strpos($line,"dev/sdb1")!==FALSE ||
                                        strpos($line,"dev/sdc1")!==FALSE ||
                                        strpos($line,"dev/sda2")!==FALSE ||
                                        strpos($line,"dev/sdb2")!==FALSE ||
                                        strpos($line,"dev/sdc2")!==FALSE ||
                                        strpos($line,"dev/sda3")!==FALSE ||
                                        strpos($line,"dev/sdb3")!==FALSE ||
                                        strpos($line,"dev/sdc3")!==FALSE ||
                                        strpos($line,"dev/sda4")!==FALSE ||
                                        strpos($line,"dev/sdb4")!==FALSE ||
                                        strpos($line,"dev/sdc4")!==FALSE ||
                                        strpos($line,"dev/sda7")!==FALSE ||
                                        strpos($line,"dev/sdb7")!==FALSE ||
                                        strpos($line,"dev/sdc7")!==FALSE
                                    )
                                {
                                    if(strpos($line,"active sync")!==false)
                                    {
                                        echo "<li><pre>".$line."</pre></li>";
                                    }
                                    else
                                    {
                                        echo "<li class=\"error\"><pre>".$line."</pre></li>";
                                    }
                                    continue;
                                }
                                //This if execlusively for the output of sorin2.txt
                                if(strpos($line,"dev/sd")!==FALSE)
                                {
                                    //Check if the device status is Ok or not else display error
                                    if(strpos($line,"ok")!== false)
                                    {
                                        echo "<li>".$line."</li>";
                                    }
                                    else
                                    {
                                        echo "<li class=\"error\">".$line."</li>";
                                    } 
                                }
                            } //End of line in file for-loop
                        } //End of file_exists
                        //Display error if the file doesn't exist
                        else
                        {
                            echo "<li class=\"error\"> File Not Found </li>";
                        }
                        echo "<br/>";
                        //Parse the smartctl output for eachmachine and then print relevant details
                        //Execute the loop twice since we have 2 disks on each of the sorinx machines
                        for($j=1;$j<=3;$j++)
                        {
                            $shortOffline=0; $extendedOffline=0; $avgCount=0; 
                            $totalLifeTime=0;$errorsLogged=true;$errorCount=0;$smartSupport=true;
                            if($j==1)
                            {
                                $filename = "smartctl/sorin".$i."-sda.txt";
                                echo "<h4 class=\"handle\" name=".$filename." > Hard Drive #1 [/dev/sda] </h4>";
                            }
                            if($j==2)
                            {
                                $filename = "smartctl/sorin".$i."-sdb.txt";
                                echo "</br><h4 class=\"handle\" name=".$filename.">Hard Drive #2 [/dev/sdb] </h4>";
                            }
                            //This special case is for sorin12 (Dennis as of 11/13/16) -> 3HDD
                            if($j==3 and $i==12)
                            {
                                $filename = "smartctl/sorin".$i."-sdc.txt";
                                echo "</br><h4 class=\"handle\" name=".$filename.">Hard Drive #3 [/dev/sdc] </h4>";
                            } //End of Special Case section (j==3 and i==12)
                            //$j is less than or equal to 2 because most of the sorinX machines have 2 drives.Sorin11 and 12 have 3
                            if($j<=2)
                            {
                                //Check if the file exists
                                if(file_exists($filename))
                                {
                                    $fh=file($filename,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
                                    //Process each line in the file
                                    foreach ($fh as $line)
                                    {
                                        //Check if smart support is configured on the disk
                                        if(strpos($line,"SMART support is:")!==FALSE)
                                        {
                                            if(strpos($line,"Unavailable")!==FALSE)
                                            {
                                                echo "<li class=\"critical\">"."$line"."</li>";
                                                $smartSupport=false;
                                                break;
                                            }
                                        }
                                        //Check if any errors are logged
                                        if(strpos($line,"No Errors Logged")!==FALSE)
                                        {
                                            $errorsLogged = false;
                                        }
                                        //If there are any errors logged, count the number of errors
                                        if(strpos($line,"Error")!==FALSE)
                                        {
                                            if(strpos($line,"occurred at disk power-on lifetime")!==FALSE)
                                            {
                                                $errorCount++;
                                            }
                                        }
                                        if(strpos($line,"Local Time is")!==FALSE)
                                        {
                                            $date_string = preg_replace('/Local Time is: /', '',$line);
                                            // Convert the string to time in seconds
                                            $updatetime = strtotime($date_string);
                                            //Caliculate the present time
                                            $now = time();
                                            //Compute the difference
                                            $timediff = $now - $updatetime;
                                            //If the difference is more than one day
                                            if($timediff > 86400)
                                            {
                                                echo "<li class=\"error\">".$line."</li>";
                                            }
                                            else
                                            {
                                                echo "<li class=\"active\">".$line."</li>";
                                            }
                                            continue;
                                        }
                                        if(strpos($line,"overall-health")!==FALSE )
                                        {
                                            if(strpos($line,"PASSED"))
                                            {
                                                echo "<li class=\"active\">".$line."</li>";
                                            }
                                            else
                                            {
                                                echo "<li class=\"critical\">".$line."</li>";
                                            }
                                            continue;
                                        }
                                        //If a pre-fail attribute contains "FAILING_NOW", mark the disk status as critical
                                        if(strpos($line,"Pre-fail")!==FALSE)
                                        {
                                            if(strpos($line,"FAILING_NOW") || strpos($line,"failing_now"))
                                            {
                                                echo "<li class=\"critical\">"."Pre-fail attribute failed"."</li>";
                                            }
                                            continue;
                                        }
                                        //If an old-age attribute is failing mark it as error
                                        if(strpos($line,"Old_age")!==FALSE)
                                        {
                                            if(strpos($line,"FAILING_NOW") || strpos($line,"failing_now"))
                                            {
                                                echo "<li class=\"error\">"."Old_age attribute failed"."</li>";
                                            }
                                            continue;
                                        }
                                        //Now compute the average life time of the disk
                                        //By considering latest 4 short offline & 4 extended offline lifetime values and computing an average
                                        if(strpos($line,"Short offline")!==FALSE && strpos($line,"Completed without error")!==FALSE && $shortOffline < 4)
                                        {
                                            $shortOffline++;
                                            if (preg_match_all('!\d+!', $line, $matches)) 
                                            {
                                                if(isset($matches[0][2]))
                                                {
                                                    if($matches[0][2] > 1000)
                                                    {
                                                        $totalLifeTime+=(int)$matches[0][2];
                                                        $avgCount++;
                                                    }
                                                }
                                            }
                                            continue;
                                        }     
                                        if(strpos($line,"Extended offline")!==FALSE && strpos($line,"Completed without error")!==FALSE && $extendedOffline < 4)
                                        {
                                            $extendedOffline++;
                                            if (preg_match_all('!\d+!', $line, $matches)) 
                                            {
                                                if(isset($matches[0][2]))
                                                {
                                                    if($matches[0][2] > 1000)
                                                    {
                                                        $totalLifeTime+=(int)$matches[0][2];
                                                        $avgCount++;
                                                    }
                                                }
                                            }
                                            continue;
                                        }
                                    } //End of for-loop to check each $line
                                    if($smartSupport)
                                    {
                                        //Compute the disk life time in days
                                        //Checking for zero inorder to avoid divide by zero exception
                                        if($avgCount!=0)
                                        {
                                            $diskLifeTime = (int)(($totalLifeTime/$avgCount)/(24*30));
                                        }
                                        else
                                        {
                                            $diskLifeTime = 0;
                                        }
                                        echo "<li>Disk Age : ".$diskLifeTime." Months </li>";
                                        //If Errors are logged display as critical element
                                        if($errorsLogged)
                                        {
                                            echo "<li class=\"critical\">".$errorCount." Error(s) Logged </li>";
                                        }
                                    }//End of $smartSupport
                                } //End of file_exists
                                //If file doesn't exist display error message
                                else
                                {
                                    echo "<li class=\"error\"> File Not Found </li>";
                                }    
                            } //End of the if($j<=2)
                            //This case statement is used to display sorin12 SMARTctl sdc drive.Modify it if more machines have 3 drives
                            if($j==3 && $i==12)
                            {
                                //Check if the file exists
                                if(file_exists($filename))
                                {
                                    $fh=file($filename,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
                                    //Process each line in the file
                                    foreach ($fh as $line)
                                    {
                                        //Check if smart support is configured on the disk
                                        if(strpos($line,"SMART support is:")!==FALSE)
                                        {
                                            if(strpos($line,"Unavailable")!==FALSE)
                                            {
                                                echo "<li class=\"critical\">"."$line"."</li>";
                                                $smartSupport=false;
                                                break;
                                            }
                                        }
                                        //Check if any errors are logged
                                        if(strpos($line,"No Errors Logged")!==FALSE)
                                        {
                                            $errorsLogged = false;
                                        }
                                        //If there are any errors logged, count the number of errors
                                        if(strpos($line,"Error")!==FALSE)
                                        {
                                            if(strpos($line,"occurred at disk power-on lifetime")!==FALSE)
                                            {
                                                $errorCount++;
                                            }
                                        }
                                        if(strpos($line,"Local Time is")!==FALSE)
                                        {
                                            $date_string = preg_replace('/Local Time is: /', '',$line);
                                            // Convert the string to time in seconds
                                            $updatetime = strtotime($date_string);
                                            //Caliculate the present time
                                            $now = time();
                                            //Compute the difference
                                            $timediff = $now - $updatetime;
                                            //If the difference is more than one day
                                            if($timediff > 86400)
                                            {
                                                echo "<li class=\"error\">".$line."</li>";
                                            }
                                            else
                                            {
                                                echo "<li class=\"active\">".$line."</li>";
                                            }
                                            continue;
                                        }
                                        if(strpos($line,"overall-health")!==FALSE)
                                        {
                                            if(strpos($line,"PASSED"))
                                            {
                                                echo "<li class=\"active\">".$line."</li>";
                                            }
                                            else
                                            {
                                                echo "<li class=\"critical\">".$line."</li>";
                                            }
                                            continue;
                                        }
                                        //If a pre-fail attribute contains "FAILING_NOW", mark the disk status as critical
                                        if(strpos($line,"Pre-fail")!==FALSE)
                                        {
                                            if(strpos($line,"FAILING_NOW") || strpos($line,"failing_now"))
                                            {
                                                echo "<li class=\"critical\">"."Pre-fail attribute failed"."</li>";
                                            }
                                            continue;
                                        }
                                        //If an old-age attribute is failing mark it as error
                                        if(strpos($line,"Old_age")!==FALSE)
                                        {
                                            if(strpos($line,"FAILING_NOW") || strpos($line,"failing_now"))
                                            {
                                                echo "<li class=\"error\">"."Old_age attribute failed"."</li>";
                                            }
                                            continue;
                                        }
                                        //Now compute the average life time of the disk
                                        //By considering latest 4 short offline & 4 extended offline lifetime values and computing an avera$
                                        if(strpos($line,"Short offline")!==FALSE && strpos($line,"Completed without error")!==FALSE && $shortOffline < 4)
                                        {
                                            $shortOffline++;
                                            if (preg_match_all('!\d+!', $line, $matches)) 
                                            {
                                                if(isset($matches[0][2]))
                                                {
                                                    if($matches[0][2] > 1000)
                                                    {
                                                        $totalLifeTime+=(int)$matches[0][2];
                                                        $avgCount++;
                                                    }
                                                }
                                            }
                                            continue;
                                        }
                                        if(strpos($line,"Extended offline")!==FALSE && strpos($line,"Completed without error")!==FALSE && $extendedOffline < 4)
                                        {
                                            $extendedOffline++;
                                            if (preg_match_all('!\d+!', $line, $matches)) 
                                            {
                                                if(isset($matches[0][2]))
                                                {
                                                    if($matches[0][2] > 1000)
                                                    {
                                                        $totalLifeTime+=(int)$matches[0][2];
                                                        $avgCount++;
                                                    }
                                                }
                                            }
                                            continue;
                                        }
                                    } //End of for-loop to process each $line
                                    if($smartSupport)
                                    {
                                        //Compute the disk life time in days
                                        //Checking for zero inorder to avoid divide by zero exception
                                        if($avgCount!=0)
                                        {
                                            $diskLifeTime = (int)(($totalLifeTime/$avgCount)/(24*30));
                                        }
                                        else
                                        {
                                            $diskLifeTime = 0;
                                        }
                                        echo "<li>Disk Age : ".$diskLifeTime." Months </li>";
                                        //If Errors are logged display as critical element
                                        if($errorsLogged)
                                        {
                                            echo "<li class=\"critical\">".$errorCount." Error(s) Logged </li>";
                                        }
                                    } //End of $smartSupport
                                } //End of file_exist
                                //If file doesn't exist display error message
                                else
                                {
                                    echo "<li class=\"error\"> File Not Found </li>";
                                }
                            } //End of if($j==3 && $i==12)
                        } //End of for($j=1;$j<=3;$j++), for 2 disks on each sorinX machines 
                        
                        #grabbing the uptime of each desktop
                        $uptimeFilename = "sorin".$i.".cnsm.csulb.edu_uptime.txt";
                        if(file_exists($uptimeFilename))
                        {   
                            echo "<br>";
                            echo "<h4>Uptime</h4>";

                            $fileContents=file_get_contents($uptimeFilename);
                            echo "<li>".$fileContents."</li>";
                        }

                        echo "</ul>"; //End of machine info
                        echo "</div>"; // End of Div
                    } // End of for-loop ($i=1;$i<=13;$i++)
                ?>
            </div> <!-- End of div for Desktops -->
            <!-- Overlay Div -->
            <div id="dialog_overlay" style="display:none"> 
            </div>
            <!-- The div for displaying the file contents -->
            <div id="slideSource" style="display:none;">
                <div id="fileDisplayHeader"> 
                </div>
                <div id="fileContent">
                </div>
            </div>
            <div id="footer">
                <h4> Copyright &copy; Sorin Lab 2014</h4>
            </div>
        </div>
    </body> 
</html>
