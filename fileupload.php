<html lang="en">
  <head>
    <title>SQL Standard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  </head>

  <?php
    $Files = array($_FILES['fileToUpload']['name']);
    $Count5 = 0;

    // Creating directory if not present.
    if (!file_exists('uploads1'))
    {
      mkdir('uploads1');
    }

    // Creating Table for displaying file names.
    echo "<div class = 'container'>";
    echo "<h4 align = 'center'>Coding Standard Tool: Version 1.0</h4>";
    echo "<table class = 'table table-bordered'>";
    echo "<th>Sr.</th>";
    echo "<th>File Names</th>";

    foreach ($_FILES['fileToUpload']['name'] as $x => $value)
    {
      $x = $x + 1;
      echo "<tr>";
      echo "<td>" . $x . "</td>";
      echo "<td>" . $value . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    // Accepting one or more files.
    $TotalCountOfFiles = count($_FILES['fileToUpload']['name']);
    for ($i=0; $i<$TotalCountOfFiles; $i++)
    {

      // The temp file path is obtained;.
      $TmpFilePath = $_FILES['fileToUpload']['tmp_name'][$i];

      // A file path needs to be present.
      if ($TmpFilePath != "")
      {
        $NewFilePath = "uploads1/" . time() . $_FILES['fileToUpload']['name'][$i];
        $ext = pathinfo($NewFilePath, PATHINFO_EXTENSION);
        $FileNames[$i]['File'] = $_FILES['fileToUpload']['name'][$i];

        // Checking the file type.
        $FileExtension = array("list","sql");
        if (in_array($ext, $FileExtension))
        {

          // Check if file already exists or not.
          if (file_exists($NewFilePath))
          {
            echo "Sorry, file already exists.";
            echo "<br>";
          }
          else
          {

            // Moving uploaded file to uploads folder, opening it reading file and accepting special characters.
           if (move_uploaded_file($TmpFilePath, $NewFilePath))
           {
             $MyFile =fopen($NewFilePath, "r")or die("Unable to open file!");
             $LineRead = file($NewFilePath);
             $FileString = implode("abc", $LineRead);
             $special = htmlspecialchars($FileString);
             $FileString1 = explode("abc", $special);
             $Read = fread($MyFile,filesize($NewFilePath));
             $StoreVari = array();
             $StoreTmpName = array();
             $StoreDropName = array();
             $LineNoKeywords = array();

             //*** No line space allow in list file.***//
             if ($ext === "list")
             {
               foreach ($FileString1 as $a => $Spaces)
               {
                 if ((empty(trim($Spaces)))== true)
                 {
                   $a = $a + 1;
                   $FileNames[$i]['list']['line_no'][] = $a;
                   $FileNames[$i]['list']['err_msg'][] = 'No line space allow in list file';
                 }
                 if (substr_count($Spaces,".")>1)
                 {
                   $a = $a + 1;
                   $FileNames[$i]['list']['line_no'][] = $a;
                   $FileNames[$i]['list']['err_msg'][] = 'You cannot use more than 1 dot(.) in database.list file';
                 }
                 $String1 = explode(" ", $Spaces);

                 //*** Find Trailing Space.***//
                 if ((count($String1) !== 1)&&empty(trim(end($String1))))
                 {
                   $a = $a + 1;
                   $FileNames[$i]['general']['line_no'][] = $a;
                   $FileNames[$i]['general']['err_msg'][] = 'Trailing space';
                 }
               }
             }

             if ($ext === "sql")
             {

               //*** Checking for first 3 lines of file.***//
               $Line1 = $FileString1[0];
               $ll1 = trim($Line1);
               $l1 = "SET ANSI_NULLS ON";
               $Line2 = $FileString1[1];
               $ll2 = trim($Line2);
               $l2 = "SET QUOTED_IDENTIFIER ON";
               $Line3 = $FileString1[2];
               $ll3 = trim($Line3);
               $l3 = "GO";
               if (($ll1!==$l1)||($ll2!==$l2)||($ll3!==$l3))
               {
                 $FileNames[$i]['SQL']['line_no'][] = 'SQL File';
                 $FileNames[$i]['SQL']['err_msg'][] = 'Please enter specific 3 lines on the top of SQL file';
               }

               foreach ($FileString1 as $l => $Space)
               {

                 //*** Find one line space before comment.***//
                 if (str_contains($Space, '--'))
                 {
                   $Preh = $l - 1;
                   $PreVall = $FileString1[$Preh];
                   if (!empty(trim($PreVall)))
                   {
                     $l = $l + 1;
                     $FileNames[$i]['general']['line_no'][] = $l;
                     $FileNames[$i]['general']['err_msg'][] = 'Please use one line space before comment';
                   }
                 }

                 $String = explode(" ", $Space);

                 //*** Find Trailing Space.***//
                 if ((count($String) !== 1)&&empty(trim(end($String))))
                 {
                   $l = $l + 1;
                   $FileNames[$i]['general']['line_no'][] = $l;
                   $FileNames[$i]['general']['err_msg'][] = 'Trailing space';
                 }

                 //*** Do not use temoprary keyword use # for temporay table and @ for table variables. ***//
                 $Patternn = "/temporary/i";
                 if (preg_match_all($Patternn, $Space, $matchess))
                 {
                   $l = $l + 1;
                   $FileNames[$i]['SQL']['line_no'][] = $l;
                   $FileNames[$i]['SQL']['err_msg'][] = "Don't use Temporary keyword use # for table and @ for variable";
                 }

                 //*** Line should not exceed 132 charcters ***//
                 if (strlen($Space) > 132)
                 {
                   $l = $l + 1;
                   $FileNames[$i]['SQL']['line_no'][] = $l;
                   $FileNames[$i]['SQL']['err_msg'][] = 'Line exceed the limit of 132 characters';
                 }

                 //*** SQl keywords in CAPS. ***//
                 $Keywordds = "/select|insert|add|distinct|create|from|alter|add|delete|truncate|asc|desc|between|drop|group by|
                               join|left join|right join|like|limit|order by|primary key|procedure|union|unique|update|view|values|
                               where/";
                 if (str_contains($Space,'/******'))
                 {
                   $StartFlowerBoxLineNo = $l;
                 }
                 if (str_contains($Space,'******/'))
                 {
                   $EndFlowerBoxLineNo = $l;
                 }
                 if ((!str_contains($Space,'--')) && preg_match_all($Keywordds, $Space, $Match))
                 {
                   array_push($LineNoKeywords,$l);
                 }

                 //*** Use space after if,for,while. ***//
                 foreach ($String as $x1 => $dd)
                 {
                   $Condition = "/IF\(|FOR\(|WHILE\(/";
                   if (preg_match_all($Condition, $dd, $Matchh))
                   {
                     foreach ($Matchh as $c => $Conditions)
                     {
                       foreach ($Conditions as $ConditionTocheck)
                       {
                         $l = $l + 1;
                         $FileNames[$i]['SQL']['line_no'][] = $l;
                         $FileNames[$i]['SQL']['err_msg'][] = 'Please use space after conditional Keyword';
                       }
                     }
                   }
                 }

                 //*** Finding space after ,(comma). ***//
                 $Patt1 = "/\,\S/";
                 if (preg_match($Patt1, $Space))
                 {
                   $l = $l + 1;
                   $FileNames[$i]['SQL']['line_no'][] = $l;
                   $FileNames[$i]['SQL']['err_msg'][] = 'Please use space after , comma';
                 }

                 //*** finding space after ;(semicolon). ***//
                 $Patt3 = "/\;\S/";
                 if (preg_match($Patt3, $Space))
                 {
                   $l = $l + 1;
                   $FileNames[$i]['SQL']['line_no'][] = $l;
                   $FileNames[$i]['SQL']['err_msg'][] = 'Please use space after ; semi-colon';
                 }

                 //*** Find Current Year are copyright year. ***//
                 if (strpos($Space, '@copyright') !== FALSE || strpos($Space, '@Copyright') !== FALSE)
                 {
                   $LineArray = explode(" ", $Space);
                   if (!in_array(date("Y"), $LineArray))
                   {
                     $l = $l + 1;
                     $FileNames[$i]['SQL']['line_no'][] = $l;
                     $FileNames[$i]['SQL']['err_msg'][] = 'Please enter current year';
                   }
                 }

                 //*** Alias Name should not same as Column Name. ***//
                 if (strpos($Space, ".") !== false)
                 {
                   $LineArrayForAliasName = explode(" ", $Space);
                   foreach ($LineArrayForAliasName as $Strings)
                   {
                     if (strpos($Strings, ".") !== false)
                     {
                       $LineArray2 = explode(".", $Strings);
                       if ($LineArray2[0] == $LineArray2[1])
                       {
                         $l = $l + 1;
                         $FileNames[$i]['SQL']['line_no'][] = $l;
                         $FileNames[$i]['SQL']['err_msg'][] = 'You cannot use alias name same as column name';
                       }
                     }
                   }
                 }

                 //*** Find Permission for SQL Entity and verify NSAWebSVC permission not given. ***//
                 if (strpos($Space, 'GRANT') !== FALSE || strpos($Space, 'grant') !== FALSE)
                 {
                   $LineArray1 = explode(" ", $Space);
                   foreach ($LineArray1 as $Values)
                   {
                     if (str_contains($Values, 'NUCOR_AR\NSAWebSvc'))
                     {
                       $l = $l + 1;
                       $FileNames[$i]['SQL']['line_no'][] = $l;
                       $FileNames[$i]['SQL']['err_msg'][] = 'Cannot give permission for SQL Entity to mention user';
                     }
                   }
                 }

                  //*** Find one line space before Go. ***//
                  if (strpos($Space, 'GO') !== FALSE && strpos($Space, '--') == FALSE)
                  {
                    $PreLine = $l - 1;
                    $PreVall = $FileString1[$PreLine];
                    if (!empty(trim($PreVall)))
                    {
                      $l = $l + 1;
                      $FileNames[$i]['SQL']['line_no'][] = $l;
                      $FileNames[$i]['SQL']['err_msg'][] = 'Please use one line space before Go';
                    }
                  }

                  //*** Find one line space between create, alter, Set NOCOUNT ON statement. ***//
                  $Words = array ('CREATE PROCEDURE','ALTER PROCEDURE','SET NOCOUNT','CREATE FUNCTION',
                                  'ALTER FUNCTION','CREATE VIEW','ALTER VIEW','CREATE TABLE','ALTER TABLE');
                  foreach ($Words as $Word)
                  {
                    if (str_contains($Space, $Word)&&($Word == 'CREATE PROCEDURE')) 
                    {
                      $r = $l;
                      $Lnext = $l + 1;
                      $Tnext = $FileString1[$Lnext];
                      if ((empty(trim($Tnext))) == true)
                      {
                        $Lnext = $Lnext + 1;
                        $FileNames[$i]['SQL']['line_no'][] = $Lnext;
                        $FileNames[$i]['SQL']['err_msg'][] = 'line space after create';
                      }

                      //*** Do not allow special character is identifiers. ***//
                      $PatternForSC = "/\S[!$%^&*-+={}|:;'?~`#@]\S/";
                      if (preg_match($PatternForSC, $Space))
                      {
                        $l = $l + 1;
                        $FileNames[$i]['SQL']['line_no'][] = $l;
                        $FileNames[$i]['SQL']['err_msg'][] = 'Please remove special character from identifier';
                      }
                    }
                    else if (str_contains($Space, $Word)&&($Word=='ALTER PROCEDURE'))
                    {
                      $r = $l;
                      $Lnextt = $l + 1;
                      $Tnextt = $FileString1[$Lnextt];
                      if ((empty(trim($Tnextt))) == true)
                      {
                        $Lnextt = $Lnextt+1;
                        $FileNames[$i]['SQL']['line_no'][] = $Lnextt;
                        $FileNames[$i]['SQL']['err_msg'][] = 'line space after alter';
                      }

                      //*** Do not allow special character is identifiers. ***//
                      $PatternForSC1 = "/\S[!$%^&*-+={}|:;'?~`@#]\S/";
                      if (preg_match($PatternForSC1, $Space))
                      {
                        $l = $l + 1;
                        $FileNames[$i]['SQL']['line_no'][] = $l;
                        $FileNames[$i]['SQL']['err_msg'][] = 'Please remove special character from identifier';
                      }
                    }
                    else if (str_contains($Space, $Word)&&($Word == 'SET NOCOUNT ON'))
                    {
                      $y = $l;
                      $Lnexttt = $l - 1;
                      $Tnexttt = $FileString1[$Lnexttt];
                      if ((empty(trim($Tnexttt)))== true)
                      {
                        $Lnexttt = $Lnexttt+1;
                        $FileNames[$i]['SQL']['line_no'][] = $Lnexttt;
                        $FileNames[$i]['SQL']['err_msg'][] = 'line space after SET NOCOUNT ON';
                      }
                      for ($ii=$r;$ii<=$y;$ii++)
                      {
                        $Val = $FileString1[$ii];
                        if ((empty(trim($Val))) == true)
                        {
                          $ii = $ii +1;
                          $FileNames[$i]['SQL']['line_no'][] = $ii;
                          $FileNames[$i]['SQL']['err_msg'][] = 'line space between alter or create procedure and SET NOCOUNT ON';
                        }
                      }
                    }
                    else if (str_contains($Space, $Word)&&($Word == 'CREATE FUNCTION'))
                    {

                      //*** Do not allow special character is identifiers. ***//
                      $PatternForSC2 = "/\S[!$%^&*-+={}|:;'?~`]\S/";
                      if (preg_match($PatternForSC2, $Space))
                      {
                        $l = $l + 1;
                        $FileNames[$i]['SQL']['line_no'][] = $l;
                        $FileNames[$i]['SQL']['err_msg'][] = 'Please remove special character from identifier';
                      }
                    }
                    else if (str_contains($Space, $Word)&&($Word == 'ALTER FUNCTION'))
                    {

                      //*** Do not allow special character is identifiers. ***//
                      $PatternForSC3 = "/\S[!$%^&*-+={}|:;'?~`]\S/";
                      if (preg_match($PatternForSC3, $Space))
                      {
                        $l = $l + 1;
                        $FileNames[$i]['SQL']['line_no'][] = $l;
                        $FileNames[$i]['SQL']['err_msg'][] = 'Please remove special character from identifier';
                      }
                    }
                    else if (str_contains($Space, $Word)&&($Word == 'CREATE VIEW'))
                    {

                      //*** Do not allow special character is identifiers. ***//
                      $PatternForSC4 = "/\S[!$%^&*-+={}|:;'?~`]\S/";
                      if (preg_match($PatternForSC4, $Space))
                      {
                        $l = $l + 1;
                        $FileNames[$i]['SQL']['line_no'][] = $l;
                        $FileNames[$i]['SQL']['err_msg'][] = 'Please remove special character from identifier';
                      }
                    }
                    else if (str_contains($Space, $Word)&&($Word == 'ALTER VIEW'))
                    {

                      //*** Do not allow special character is identifiers. ***//
                      $PatternForSC5 = "/\S[!$%^&*-+={}|:;'?~`]\S/";
                      if (preg_match($PatternForSC5, $Space))
                      {
                        $l = $l + 1;
                        $FileNames[$i]['SQL']['line_no'][] = $l;
                        $FileNames[$i]['SQL']['err_msg'][] = 'Please remove special character from identifier';
                      }
                    }
                    else if (str_contains($Space, $Word)&&($Word == 'CREATE TABLE'))
                    {

                      //*** Do not allow special character is identifiers. ***//
                      $PatternForSC6 = "/\S[!$%^&*-+={}|:;'?~`]\S/";
                      if (preg_match($PatternForSC6, $Space))
                      {
                        $l = $l + 1;
                        $FileNames[$i]['SQL']['line_no'][] = $l;
                        $FileNames[$i]['SQL']['err_msg'][] = 'Please remove special character from identifier';
                      }
                    }
                    else if (str_contains($Space, $Word)&&($Word == 'ALTER TABLE'))
                    {

                      //*** Do not allow special character is identifiers. ***//
                      $PatternForSC7 = "/\S[!$%^&*-+={}|:;'?~`]\S/";
                      if (preg_match($PatternForSC7, $Space))
                      {
                        $l = $l + 1;
                        $FileNames[$i]['SQL']['line_no'][] = $l;
                        $FileNames[$i]['SQL']['err_msg'][] = 'Please remove special character from identifier';
                      }
                    }
                  }

                  //*** One line space after begin and before end. ***//
                  if (str_contains($Space, 'BEGIN'))
                  {
                    $Nex = $l + 1;
                    $Vnex = $FileString1[$Nex];
                    if ((!empty(trim($Vnex))) == true)
                    {
                      $FileNames[$i]['SQL']['line_no'][] = $Nex;
                      $FileNames[$i]['SQL']['err_msg'][] = 'please enter one line space after BEGIN';
                    }
                  }
                  else if (str_contains($Space, 'END'))
                  {
                    $Pre = $l - 1;
                    $Vpre = $FileString1[$Pre];
                    if ((!empty(trim($Vpre))) == true)
                    {
                      $Pre = $Pre + 1;
                      $FileNames[$i]['SQL']['line_no'][] = $Pre;
                      $FileNames[$i]['SQL']['err_msg'][] = 'Please enter one line space before END';
                    }
                  }

                  //*** space between datatype and input. ***//
                  if (str_contains($Space,')(INPUT)'))
                  {
                    $l = $l + 1;
                    $FileNames[$i]['SQL']['line_no'][] = $l;
                    $FileNames[$i]['SQL']['err_msg'][] = 'Enter space between Datatype and Input';
                  }


                  //*** Unused variable ***//
                  $Curind = (array_search("DECLARE", $String));
                  if ($Curind !== false)
                  {
                    $Nexind = $Curind+1;
                    $Varicount = $String[$Nexind];

                    //*** Do not allow special character in identifiers. ***//
                    $PatternForSC8 = "/\S[!$%^&*-+={}|:;'?~`]\S/";
                    if (preg_match($PatternForSC8, $Varicount))
                    {
                      $l = $l + 1;
                      $FileNames[$i]['SQL']['line_no'][] = $l;
                      $FileNames[$i]['SQL']['err_msg'][] = 'Please remove special character from identifier';
                    }
                    array_push ($StoreVari,$Varicount);
                  }

                  //*** Tmp table created is dropped for not. ***//
                  $TmpTblName = (array_search("CREATE", $String));
                  if ($TmpTblName !== false)
                  {
                    $FindTable = $TmpTblName + 1;
                    if ($String[$FindTable] == 'TABLE')
                    {
                      $FindHash = $FindTable + 1;
                      $FindHashVal = $String[$FindHash];
                      if (str_contains($FindHashVal, '#'))
                      {
                        array_push($StoreTmpName, $FindHashVal);
                      }
                    }
                  }
                  $DrpTblName = (array_search("DROP", $String));
                  if ($DrpTblName !== false)
                  {
                    $FindDTable = $DrpTblName + 1;
                    if ($String[$FindDTable] == 'TABLE')
                    {
                      $FindDHash = $FindDTable + 1;
                      $FindDHashVal = $String[$FindDHash];
                      if (str_contains($FindDHashVal, '#'))
                      {
                        $FindDHashVal = str_replace(';', '', $FindDHashVal);
                        array_push ($StoreDropName,$FindDHashVal);
                      }
                    }
                  }

                  //*** Find 5 revision in flower box. ***//
                  $Pattern = "%[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])%";
                  if (preg_match_all($Pattern, $Space, $Matches1))
                  {
                    $Count5 = $Count5 + 1;
                  }
              }
              if ($Count5<5)
              {
                $FileNames[$i]['SQL']['line_no'][] = 'SQL File';
                $FileNames[$i]['SQL']['err_msg'][] = 'Please use at least 5 revision in flower pot';
              }

              //Exploding to use Special characters as well.
              $WithSpChar = explode("abc", $FileString);

              //*** finding double quotes. ***//
              foreach ($WithSpChar as $t => $Valuee)
              {
                $Patt1 = '/\"/';
                if (preg_match($Patt1, $Valuee))
                {
                  $t= $t + 1;
                  $FileNames[$i]['SQL']['line_no'][] = $t;
                  $FileNames[$i]['SQL']['err_msg'][] = 'you cannot use double quote in SQl Script';
                }

                //*** Use BEGIN and END keywords only when IF/WHILE statements are multiline. ***//
                if (strpos($Valuee, 'IF') !== FALSE)
                {
                  $CurrentIndex = $t;
                  $NextIndexBegin = $t + 1;
                  $NextIndexEnd = $NextIndexBegin + 2;
                  $FindBeginNext = $WithSpChar[$NextIndexBegin];
                  $FindBeginEnd = $WithSpChar[$NextIndexEnd];
                  if ((strpos($FindBeginNext, 'BEGIN') !== FALSE))
                  {
                    $NextIndexBegin = $NextIndexBegin + 1;
                    if ((strpos($FindBeginEnd, 'END') !== FALSE))
                    {
                      $t = $t + 1;
                      $FileNames[$i]['SQL']['line_no'][] = $t;
                      $FileNames[$i]['SQL']['err_msg'][] = 'For One line conditional statements you cannot use Begin and End';
                    }
                  }
                }

                //*** Not begin string with wildcard when using LIKE if possible. ***//
                if (strpos($Valuee, 'LIKE') !== FALSE)
                {
                  if ((str_contains($Valuee, '%'))||(str_contains($Valuee, '_')))
                  {
                    $t= $t + 1;
                    $FileNames[$i]['SQL']['line_no'][] = $t;
                    $FileNames[$i]['SQL']['err_msg'][] = 'Please Not begin string with wildcard when using LIKE if possible';
                  }
                }

                //*** Verify if the stored procedure contains calling of another SP then specify their parameter. ***//
                $PatternForExec = "/EXEC\s/";
                if (preg_match($PatternForExec, $Valuee))
                {
                  $IndexOfExec = $t;
                  $IndexOfNext = $IndexOfExec + 1;
                  $NextIndexVal = $WithSpChar[$IndexOfNext];
                  if ((strpos($NextIndexVal, '@') !== FALSE)&& (strpos($NextIndexVal, '=') == FALSE))
                  {
                    $IndexOfNext = $IndexOfNext + 1;
                    $FileNames[$i]['SQL']['line_no'][] = $IndexOfNext;
                    $FileNames[$i]['SQL']['err_msg'][] = 'Please specify parameter for calling another stored procedure';
                  }
                }

                //*** use AND, OR,<> instead of &&,||,!=. ***//
                $SpChars = array("&&","||","!=");
                foreach ($SpChars as $Chars) 
                {
                  $CharsPos = strrpos($Valuee, $Chars);
                  if (!empty($CharsPos) && $Chars == "&&") 
                  {
                    $t=$t+1;
                    $FileNames[$i]['SQL']['line_no'][] = $t;
                    $FileNames[$i]['SQL']['err_msg'][] = 'Please use AND instead of &&';
                  }

                  elseif (!empty($CharsPos) && $Chars == "||")
                  {
                    $t=$t+1;
                    $FileNames[$i]['SQL']['line_no'][] = $t;
                    $FileNames[$i]['SQL']['err_msg'][] = 'Please use OR instead of ||';
                  }

                  elseif (!empty($CharsPos) && $Chars == "!=")
                  {
                    $t=$t+1;
                    $FileNames[$i]['SQL']['line_no'][] = $t;
                    $FileNames[$i]['SQL']['err_msg'][] = 'Please use <> instead of !=';
                  }
                }

                //*** Find alias name are in camel case or not. ***//
                if ((stripos($Valuee," AS ") !== false))
                {
                  $FindAliasName = explode(" ",$Valuee);
                  $SearchForASInArray = array_search("AS",$FindAliasName);
                  $NextValOfAS = $SearchForASInArray + 1;
                  $StoreAliasName = $FindAliasName[$NextValOfAS];
                  if (!empty($StoreAliasName))
                  {
                    $FindFirstLetter = str_split($StoreAliasName);
                    if (ctype_lower($FindFirstLetter[0]))
                    {
                      $t = $t + 1;
                      $FileNames[$i]['SQL']['line_no'][] = $t;
                      $FileNames[$i]['SQL']['err_msg'][] = 'Please use alias name in camel case only';
                    }
                  }
                }

                //*** Accept SCOPE_IDENTITY() instead of @@IDENTITY. ***//
                if (str_contains($Valuee,'@@IDENTITY'))
                {
                  $t = $t + 1;
                  $FileNames[$i]['SQL']['line_no'][] = $t;
                  $FileNames[$i]['SQL']['err_msg'][] = 'Please use SCOPE_IDENTITY() instead of @@IDENTITY ';
                }

                //*** Always use IS NULL or IS NOT NULL instead of using comparison operator (=) NULL ***//
                if (!str_contains($Valuee,') = NULL')&& str_contains($Valuee,'= NULL'))
                {
                   $t = $t + 1;
                   $FileNames[$i]['SQL']['line_no'][] = $t;
                   $FileNames[$i]['SQL']['err_msg'][] = 'Plese use IS NULL instead of = NULL';
                }

                if (!str_contains($Valuee,') = NOT NULL')&& str_contains($Valuee,'= NOT NULL'))
                {
                  $t = $t + 1;
                  $FileNames[$i]['SQL']['line_no'][] = $t;
                  $FileNames[$i]['SQL']['err_msg'][] = 'Plese use IS NOT NULL instead of = NOT NULL';
                }
              }

              //*** Not allow * instead use column name. ***//
              $Matchess  = preg_grep('/^SELECT\s\*/i',$WithSpChar);
              foreach ($Matchess as $x1 => $val1)
              {
                $x1 = $x1 + 1;
                $FileNames[$i]['SQL']['line_no'][] = $x1;
                $FileNames[$i]['SQL']['err_msg'][] = 'Please retrive column using column name instead of *';
              }

              //*** Array difference to find if temporary table is dropped or not. ***//
              $result = array_diff($StoreTmpName,$StoreDropName);
              $CountElements = count($result);
              if ($CountElements>0)
              {
                $FileNames[$i]['SQL']['line_no'][] = 'SQL';
                $FileNames[$i]['SQL']['err_msg'][] = 'There is temporary table which is not dropped';
              }

              //*** Finding Variable that are unused. ***//
              foreach ($StoreVari as $v => $Variable)
              {
                $Strcount = substr_count($Read,$Variable);
                if (($Strcount>1) == False)
                {
                  $FileNames[$i]['SQL']['line_no'][] = 'My SQL';
                  $FileNames[$i]['SQL']['err_msg'][] = 'There is unused variable in file';
                }
              }

              //*** Checking for SQL Keywords should not be in comments. ***//
              foreach ($LineNoKeywords as $LineNo)
              {
                if ($LineNo < $StartFlowerBoxLineNo || $LineNo > $EndFlowerBoxLineNo)
                {
                  $LineNo = $LineNo + 1;
                  $FileNames[$i]['SQL']['line_no'][] = $LineNo;
                  $FileNames[$i]['SQL']['err_msg'][] = 'Please use SQL Keywords in CAPS';
                }
              }

               // Closing the file.
               fclose($MyFile);
             }
             else
             {
               echo "Sorry, file not uploaded, please try again!";  
             }
           }
         }
        }
        else
        {
          echo "Sorry, only sql,list files are allowed.";
        }
      }
    }

    $Log_array = array_values($FileNames);

    echo "<div class='container'>";
    echo "<table class = 'table table-bordered'>";

    if (count($Log_array) != 0)
    {
      $TotalFilesError = 0;
      for ($l=0; $l < count($Log_array); $l++)
      {
        if (isset($Log_array[$l]['list']['line_no']) && isset($Log_array[$l]['general']['line_no'])
            && !isset($Log_array[$l]['SQL']['line_no']))
        {
          $TotalFilesError = $TotalFilesError + count($Log_array[$l]['general']['line_no']) + count($Log_array[$l]['list']['line_no']);
        }

        else if (!isset($Log_array[$l]['list']['line_no']) && isset($Log_array[$l]['general']['line_no'])
                 && isset($Log_array[$l]['SQL']['line_no']))
        {
          $TotalFilesError = $TotalFilesError + count($Log_array[$l]['general']['line_no']) + count($Log_array[$l]['SQL']['line_no']);
        }
      }
      echo "<tr><td colspan='3'><b>Total File Errors : " .$TotalFilesError. "
            <i class='fa fa-warning' style='font-size:20px'></i></b></td></tr>";
      echo "<tr>
             <th><i class='fa fa-list-ol' style='font-size:18px '></i> Index</th>
             <th><i class='fa fa-align-justify' style='font-size:18px '></i> Line No.</th>
             <th><i class='fa fa-ban' style='font-size:18px' ></i> Standard Deviation</th>
            </tr>";
      for ($l=0; $l < count($Log_array); $l++)
      {
        if (isset($Log_array[$l]['list']))
        {
          echo "<tr><td colspan='3' class='text-center'><b>" .$Log_array[$l]['File']. "
                <i class='fa fa-file-code-o' style='font-size:24px'></i></b></td></tr>";

          echo "<tr><td colspan='3'><b>Total errors: " .count($Log_array[$l]['list']['line_no']). "
                <i class='fa fa-warning' style='font-size:20px'></i></b></td></tr>";
          for ($p=0; $p < count($Log_array[$l]['list']['line_no']) ;$p++)
          {
            echo "<tr><td>".($p+1)."</td><td>" .$Log_array[$l]['list']['line_no'][$p]. "</td><td>"
                 .$Log_array[$l]['list']['err_msg'][$p]. "</td></tr>";
          }
        }
        if (isset($Log_array[$l]['SQL']))
        {
          echo "<tr><td colspan='3' class='text-center'><b>" .$Log_array[$l]['File']. "
                <i class='fa fa-file-code-o' style='font-size:24px'></i></b></td></tr>";
          echo "<tr><td colspan='3'><b>Total errors: " .count($Log_array[$l]['SQL']['line_no']). "
                <i class='fa fa-warning' style='font-size:20px'></i></b></td></tr>";
          for ($p=0; $p < count($Log_array[$l]['SQL']['line_no']) ;$p++)
          {
            echo "<tr><td>" .($p+1). "</td><td>" .$Log_array[$l]['SQL']['line_no'][$p]. "</td><td>"
                  .$Log_array[$l]['SQL']['err_msg'][$p]. "</td></tr>";
          }
        }
        if (isset($Log_array[$l]['general']))
        {
          echo "<tr><td colspan='3' class='text-center'><b>General Errors in " .$Log_array[$l]['File']. "
                <i class='fa fa-file-code-o' style='font-size:24px'></i></b></td></tr>";
          echo "<tr><td colspan='3'><b>Total errors: " .count($Log_array[$l]['general']['line_no']). "
                <i class='fa fa-warning' style='font-size:20px'></i></b></td></tr>";
          for ($p=0; $p < count($Log_array[$l]['general']['line_no']) ;$p++)
          {
            echo "<tr><td>" .($p+1). "</td><td>" .$Log_array[$l]['general']['line_no'][$p]. "</td><td>"
                  .$Log_array[$l]['general']['err_msg'][$p]. "</td></tr>";
          }
        }
      }
    }
    echo "</table>";
    echo "</div>";
  ?>
