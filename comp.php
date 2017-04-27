  <?php 
   // size input prevents buffer overrun exploits.
   function sizeinput($input, $len){
        (int)$len;
  	 (string)$input;
  	 $n = substr($input, 0,$len);
	 $ret = trim($n);
 	 $out = htmlentities($ret, ENT_QUOTES);
 	 return $out;
}
 //Check the file is of correct format.  
function checkfile($input){
    $ext = array('mpg', 'wma', 'mov', 'flv', 'mp4', 'avi', 'qt', 'wmv', 'rm');
    $extfile = substr($input['name'],-4); 
    $extfile = explode('.',$extfile);
    $good = array();
    $extfile = $extfile[1];
    if(in_array($extfile, $ext)){
          $good['safe'] = true;
 		 $good['ext'] = $extfile;
    }else{
          $good['safe'] = false;
   }
     return $good;
 }

 // if the form was submitted process request if there is a file for uploading
 if($_POST && array_key_exists("vid_file", $_FILES)){
                           //$uploaddir is for videos before conversion
        $uploaddir = 'uploads/videos/';
                           //$live_dir is for videos after converted to flv
 		$live_dir = 'uploads/live/';
                            //$live_img is for the first frame thumbs.
 		$live_img = 'uploads/images/';		
                           $seed = rand(1,2009) * rand(1,10);		 
 		$upload = $seed. basename($_FILES['vid_file']['name']);
 		$uploadfile = $uploaddir .$upload;        
 		$vid_title = sizeinput($_POST['vid_title'], 50);
		$vid_desc = sizeinput($_POST['vid_description'], 200);
                           $vid_cat = (int)$_POST['vid_cat'];
 		$vid_usr_ip = $_SERVER['REMOTE_ADDR'];
        	             $safe_file = checkfile($_FILES['vid_file']);
 		if($safe_file['safe'] == 1){
                                if (move_uploaded_file($_FILES['vid_file']['tmp_name'], $uploadfile)) {
                                       echo "File is valid, and was successfully uploaded.<br/>";
 					$base = basename($uploadfile, $safe_file['ext']);
 					$new_file = $base.'flv';
					$new_image = $base.'jpg';
 					$new_image_path = $live_img.$new_image;
 					$new_flv = $live_dir.$new_file;
						//ececute ffmpeg generate flv
                    exec('ffmpeg -i '.$uploadfile.' -f flv -s 320x240 '.$new_flv.'');
                       //execute ffmpeg and create thumb
					exec('ffmpeg  -i '.$uploadfile.' -f mjpeg -vframes 1 -s 150x150 -an '.$new_image_path.'');
			echo 'Thank You For Your Video!<br>';
                       //create query to store video

 		$sql = 'INSERT INTO videos (vid_cat_id, vid_title, vid_desc, vid_file_name, image_file, vid_usr_ip) VALUES(\''.$vid_cat.'\' , \''.$vid_title.'\', \''.$vid_desc.'\', \''.$new_file.'\', \''.$new_image.'\', \''.$vid_usr_ip.'\')';
 					$mysql = new mysqli('localhost','root','','video_db');
					echo '<img src="'.$new_image_path.'" /><br/>
 					      <h3>'.$vid_title.'</h3>';
 				    mysqli_query($mysql, $sql) or die(mysqli_error($mysql));
             } else {
                    echo "Possible file upload attack!\n";
			        print_r($_FILES);
             }
 
  		}else{
		
		     echo 'Invalid File Type Please Try Again. You file must be of type 
 			 .mpg, .wma, .mov, .flv, .mp4, .avi, .qt, .wmv, .rm';
 		
 		}
 }
 ?>
 <form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
  <p align="left">Please upload Your video.  Thumbnails of your videos are based on the first frame of your video. <br /><h3>Please allow up to a minute for your video to upload. </h3>
   </p>
  <table width="600" border="0" align="center" cellpadding="2" cellspacing="2">
  <tr>
    <td width="260" align="left" colspan="3"><div align="center">
      <h3>Upload your Video ! </h3>
    </div></td>
   </tr>
    <tr>
      <td width="260"  align="left"></td>
      <td width="326" align="left"></td>
    </tr>
    <tr>
      <td align="left">Title Of Video : </td>
      <td align="left"><input name="vid_title" type="text" id="vid_title" /></td>
   </tr>
   <tr>
     <td align="left">File: .mov, .avi, .wma , .mpeg : </td>
     <td align="left"><input name="vid_file" type="file" id="vid_file" /></td>
  </tr>
   <tr>
     <td align="left">Description:</td>
    <td align="left"><textarea name="vid_description" id="vid_description"></textarea></td>
  </tr>
   <tr>
     <td align="left">Category:</td>
    <td align="left"><select name="vid_cat">
       <option value="1" selected="selected">Video</option>
       <option value="2">Cat1</option>
      <option value="3">Cat2</option>
      <option value="4">Cat3</option>
      <option value="5">Cat4</option>
    </select>
     </td>
  </tr>
  <tr>
    <td></td>
     <td><input type="submit" name="Submit" value="Upload Video" /></td>
  </tr>
 </table>
 
</form>