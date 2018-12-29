# UploadFile
Eaay Library UploadFile

## Usage

โครงสร้าง HTML FORM:

```html
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="myuploadfil" value=""/>
    <input type="submit" value="Upload File"/>
</form>
```

ส่งข้อมูลไปยัง server-side
```php
<?php 

  include_once 'UploadFile.php';

  $file = new UploadFile( 'file-plugin-zip' );
  $file->setDir( '/path/to/directory' );
  $file->setName( date( 'd_m_y_h_i_s' ) . '_' . uniqid( ) );
  $file->setAllow( [ 'jpg', 'jpeg', 'png', 'gif' ] );
  $file->setSize( 2097152 );

  // Upload File.
  if ( $file->upload( ) ) {
    // Success!
    // Access data about the file that has been uploaded
    $data = array(
        'name'       => $file->getNameWithExtension(),
        'extension'  => $file->getExtension(),
        'mime'       => $file->getMimetype(),
        'size'       => $file->getSize(),
        'md5'        => $file->getMd5(),
        'dimensions' => $file->getDimensions(),
        'dir'        => $file->getDir(),
        'fullpath'   => $file->getFullPath(),
        'fullurl'    => $file->getFullUrl()
    );
  } else {
    $errors = $file->getErrors();
  }

?>
```
