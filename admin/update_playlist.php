<?php

include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:login.php');
}

if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   $get_id = '';
   header('location:playlist.php');
}

if(isset($_POST['submit'])){

   $title = $_POST['title'];
   $title = filter_var($title, FILTER_SANITIZE_STRING);
   $description = $_POST['description'];
   $description = filter_var($description, FILTER_SANITIZE_STRING);
   $status = $_POST['status'];
   $status = filter_var($status, FILTER_SANITIZE_STRING);

   $update_playlist = $conn->prepare("UPDATE `playlist` SET title = ?, description = ?, status = ? WHERE id = ?");
   $update_playlist->execute([$title, $description, $status, $get_id]);

   $old_image = $_POST['old_image'];
   $old_image = filter_var($old_image, FILTER_SANITIZE_STRING);
   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $ext = pathinfo($image, PATHINFO_EXTENSION);
   $rename = unique_id().'.'.$ext;
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../uploaded_files/'.$rename;

   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'O tamanho da imagem é muito grande!';
      }else{
         $update_image = $conn->prepare("UPDATE `playlist` SET thumb = ? WHERE id = ?");
         $update_image->execute([$rename, $get_id]);
         move_uploaded_file($image_tmp_name, $image_folder);
         if($old_image != '' AND $old_image != $rename){
            unlink('../uploaded_files/'.$old_image);
         }
      }
   } 

   $message[] = 'Playlist atualizada!';  

}

if(isset($_POST['delete'])){
   $delete_id = $_POST['playlist_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);
   $delete_playlist_thumb = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? LIMIT 1");
   $delete_playlist_thumb->execute([$delete_id]);
   $fetch_thumb = $delete_playlist_thumb->fetch(PDO::FETCH_ASSOC);
   unlink('../uploaded_files/'.$fetch_thumb['thumb']);
   $delete_bookmark = $conn->prepare("DELETE FROM `bookmark` WHERE playlist_id = ?");
   $delete_bookmark->execute([$delete_id]);
   $delete_playlist = $conn->prepare("DELETE FROM `playlist` WHERE id = ?");
   $delete_playlist->execute([$delete_id]);
   header('location:playlists.php');
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Atualizar lista de reprodução</title>
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>
   
<section class="playlist-form">
   <h1 class="heading">Atualizar lista de reprodução</h1>

   <?php
         $select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ?");
         $select_playlist->execute([$get_id]);
         if($select_playlist->rowCount() > 0){
         while($fetch_playlist = $select_playlist->fetch(PDO::FETCH_ASSOC)){
            $playlist_id = $fetch_playlist['id'];
            $count_videos = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ?");
            $count_videos->execute([$playlist_id]);
            $total_videos = $count_videos->rowCount();
      ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="old_image" value="<?= $fetch_playlist['thumb']; ?>">
      <p>Status da lista de reprodução <span>*</span></p>
      <select name="status" class="box" required>
         <option value="<?= $fetch_playlist['status']; ?>" selected><?= $fetch_playlist['status']; ?></option>
         <option value="active">active</option>
         <option value="deactive">deactive</option>
      </select>
      <p>Título da playlist <span>*</span></p>
      <input type="text" name="title" maxlength="100" required placeholder="Insira o título da lista de reprodução" value="<?= $fetch_playlist['title']; ?>" class="box">
      <p>Descrição da playlist <span>*</span></p>
      <textarea name="description" class="box" required placeholder="Escrever descrição" maxlength="1000" cols="30" rows="10"><?= $fetch_playlist['description']; ?></textarea>
      <p>Miniatura da lista de reprodução <span>*</span></p>
      <div class="thumb">
         <span><?= $total_videos; ?></span>
         <img src="../uploaded_files/<?= $fetch_playlist['thumb']; ?>" alt="">
      </div>
      <input type="file" name="image" accept="image/*" class="box">
      <input type="submit" value="Atualizar lista de reprodução" name="submit" class="btn">
      <div class="flex-btn">
         <input type="submit" value="delete" class="delete-btn" onclick="return confirm('Excluir esta lista de reprodução?');" name="delete">
         <a href="view_playlist.php?get_id=<?= $playlist_id; ?>" class="option-btn">Ver lista de reprodução</a>
      </div>
   </form>
   <?php
      } 
   }else{
      echo '<p class="empty">Nenhuma lista de reprodução adicionada ainda!</p>';
   }
   ?>

</section>



<?php include '../components/footer.php'; ?>

   <script src="../js/admin_script.js"></script>
</body>
</html>

<!--
    Autor: Daniel Oliveira
    Email: danieloliveira.webmaster@gmail.com
    Manaus/Amazonas
    06/02/2023
-->