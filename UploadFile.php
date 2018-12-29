<?php 
/**
 * @author 		Aphagon < https://www.fb.com/vilet.sz >
 * @link 		https://aphagon.me
 * @package 	Sloth Framework
 * @since 		1.0.0
 */

if ( ! class_exists( 'UploadFile' ) ) {
    class UploadFile {

        /**
         * Upload error messages
         * @var array
         */
        protected $errorMessages = array(
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stopped the file upload'
        );

        /**
         * Validation errors
         * @var array[String]
         */
        protected $errors = array( );

        /**
         * Get Files
         * @var array
         */
        protected $fileInfo;

        /**
         * File Name.
         * 
         * @var array
         */
        protected $file_name;

        /**
         * File extension (without dot prefix)
         * 
         * @var string
         */
        protected $extension;

        /**
         * Allow extension file.
         * 
         * @var array|string
         */
        protected $allowed = null;

        /**
         * File Size.
         */
        protected $maxSize; // Min Size 2MB.

        /**
         * Path Directory.
         * 
         * @var string
         */
        protected $directory;

        public function __construct( $key ) {
            
            // Check if file uploads are allowed.
            if ( ini_get( 'file_uploads' ) == false ) {
                $this->errors = array( 'File uploads are disabled in your PHP.ini file' );
                return false;
            }

            // Check if key exists.
            if ( empty( $_FILES[ $key ] ) ) {
                $this->errors = array( 'Cannot find uploaded file(s) identified by key: ' . $key );
                return false;
            }

            if ( $_FILES[ $key ][ 'error' ] !== UPLOAD_ERR_OK ) {
                $this->errors[] = sprintf(
                    '%s: %s',
                    $_FILES[ $key ][ 'name' ],
                    $this->errorMessages[ $_FILES[ $key ][ 'error' ] ]
                );
                return false;
            }

            $this->fileInfo = $_FILES[ $key ];
            $this->createFromFactory( );
        }

        /**
         * createFromFactory.
         */
        protected function createFromFactory( ) {
            $this->setName( pathinfo( $this->fileInfo[ 'name' ], PATHINFO_FILENAME ) );
            $this->setExtension( pathinfo( $this->fileInfo[ 'name' ], PATHINFO_EXTENSION ) );
            $this->setSize( 2097152 );
        }

        /**
         * Get File Size
         *
         * @return int
         */
        public function getSize( ) {
            return $this->fileInfo[ 'size' ];
        }

        /**
         * Set maxSize.
         * 
         * @param int
         */
        public function setSize( $maxSize ) {
            $this->maxSize = $maxSize;
            return $this;
        }

        /**
         * Get file name (without extension)
         *
         * @return string
         */
        public function getName( ) {
            return $this->file_name;
        }

        /**
         * Set file name (without extension)
        * 
         * It also makes sure file name is safe
         *
         * @param  string $name
         */
        public function setName( $file_name ) {
            $file_name = preg_replace( '/([^\w\s\d\-_~,;:\[\]\(\).]|[\.]{2,})/', '', $file_name);
            $file_name = basename( $file_name );
            $this->file_name = $file_name;
            return $this;
        }
        
        /**
         * Get Path Directory.
         * 
         * @return string
         */
        public function getDir( ) {
            return $this->directory;
        }

        /**
         * Set Path Directory.
         * 
         * @param string $directory
         */
        public function setDir( $directory ) {
            $this->directory = rtrim( $directory, '/' );
            return $this;
        }

        /**
         * Create Directory.
         * 
         * @return bool TRUE|FALSE
         */
        protected function createDirectory( ) {
            if ( $this->directory == false ) 
                $this->directory = 'MyUploads';
            if ( ! is_dir( $this->directory ) ) 
                mkdir( $this->directory, 0777, true );
            if ( ! is_writable( $this->directory ) ) {
                $this->errors[] = 'Directory is not writable';
                return false;
            }
            return true;
        }

        /**
         * Set Allow File.
         * 
         * @param array|string $allowed
         */
        public function setAllow( $allowed ) {
            $this->allowed = $allowed;
            return $this;
        }

        /**
         * Set file extension (without dot prefix)
         *
         * @param  string $extension
         */
        public function setExtension( $extension ) {
            $this->extension = strtolower( $extension );
            return $this;
        }

        /**
         * Get file extension (without dot prefix)
         *
         * @return string
         */
        public function getExtension( ) {
            return $this->extension;
        }

        /**
         * Get file name with extension
         *
         * @return string
         */
        public function getNameWithExtension( ) {
            return $this->extension === '' ? $this->file_name : sprintf( '%s.%s', $this->file_name, $this->extension );
        }

        /**
         * Get mimetype
         *
         * @return string
         */
        public function getMimetype( ) {
            return $this->fileInfo[ 'type' ];
        }

        /**
         * Get md5
         *
         * @return string
         */
        public function getMd5( ) {
            return md5_file( $this->fileInfo[ 'tmp_name' ] );
        }

        /**
         * Get a specified hash
         *
         * @return string
         */
        public function getHash( $algorithm = 'md5' ) {
            return hash_file( $algorithm, $this->fileInfo[ 'tmp_name' ] );
        }
        
        /**
         * Get image dimensions
         *
         * @return array formatted array of dimensions
         */
        public function getDimensions( ) {
            list( $width, $height ) = getimagesize( $this->fileInfo[ 'tmp_name' ] );
            return array(
                'width' => $width,
                'height' => $height
            );
        }

        /**
         * Nice formatting for computer sizes (Bytes).
         *
         * @param   integer $bytes The number in bytes to format
         * @return  string
         */
        public function size_format( $bytes ) {
            $bytes = floatval($bytes);
            if ($bytes < 1024) {
                return $bytes . ' B';
            } elseif ($bytes < pow(1024, 2)) {
                return number_format($bytes / 1024, 0, '.', '') . ' KiB';
            } elseif ($bytes < pow(1024, 3)) {
                return number_format($bytes / pow(1024, 2), 0, '.', '') . ' MiB';
            } elseif ($bytes < pow(1024, 4)) {
                return number_format($bytes / pow(1024, 3), 0, '.', '') . ' GiB';
            } elseif ($bytes < pow(1024, 5)) {
                return number_format($bytes / pow(1024, 4), 0, '.', '') . ' TiB';
            } elseif ($bytes < pow(1024, 6)) {
                return number_format($bytes / pow(1024, 5), 0, '.', '') . ' PiB';
            } else {
                return number_format($bytes / pow(1024, 5), 0, '.', '') . ' PiB';
            }
        }

        /**
         * Is this file upload with a POST request?
         *
         * This is a separate method so that it can be stubbed in unit tests to avoid
         * the hard dependency on the `is_uploaded_file` function.
         *
         * @return bool
         */
        public function isUpload( ) {
            return is_uploaded_file( $this->fileInfo[ 'tmp_name' ] );
        }

        /**
         * Is this collection valid and without errors?
         *
         * @return bool
         */
        public function isValid( ) {

            if ( isset( $this->allowed ) && ! is_array( $this->allowed ) )
                $this->allowed = array( $this->allowed );
            if ( isset( $this->allowed ) && ! in_array( $this->extension, $this->allowed ) ) 
                $this->errors[] = sprintf( 'This file extension is not allowed. Please upload a (%s) file.', implode( ', ', $this->allowed ) );
            if ( $this->getSize( ) > $this->maxSize )
                $this->errors[] = sprintf( 'This file is larger than %s.', $this->size_format( $this->maxSize ) );
            
            // Check is uploaded file
            if ( $this->isUpload( ) === false ) {
                $this->errors[] = sprintf(
                    '%s: %s',
                    $this->getNameWithExtension( ),
                    'Is not an uploaded file'
                );
            }

            return empty( $this->errors );
        }

        /**
         * Get file validation errors
         *
         * @return array[String]
         */
        public function getErrors( ) {
            return $this->errors;
        }

        /**
         * Upload
         */
        public function upload( ) {
            if ( $this->isValid( ) === false) 
                return false;
            if ( $this->createDirectory( ) === true ) {
                $file_upload = $this->directory . '/' . $this->getNameWithExtension( );
                if ( move_uploaded_file( $this->fileInfo[ 'tmp_name' ], $file_upload ) === false ) {
                    $this->errors = array( sprintf( 'Unable to upload the file. Check folder to path %s permission 777', $this->directory ) );
                    return false;
                }
            } else {
                $this->errors = array( 'Not found Directory.' );
                return false;
            }
            return true;
        }

    }
}
