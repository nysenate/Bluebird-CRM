This folder contains file type detection classes. To add new file type
detection, the only thing you have to do is to make a class in this directory.
The name of the class must be begins with "type_" followed by identification
word, which can be used in 'types' config setting (*docs). For example if your
class name is 'type_docs', you can define in config.php:

    'types' => array(
        ......
        'docsFolder' => "*docs",
        ......
    ),

The class must contain a public method named checkFile(), which is called on
file upload and rename, and returns true if the file is verified or an error
string if not. The method has two input parametters. The first one gets
filename path of checked file. The second is an array and gets KCFinder
configuration settings. An additional 'params' element is added to the
settings array. The 'params' array element contains additional paramettes
right after *docs if any. For example if your 'types' setting is:

    'types' => array(
        ......
        'docsFolder' => "*docs pdf doc xls",
        ......
    ),

the 'params' element will contain "pdf doc xls". So let see some example code,
which can detect filename extension:


    class type_docs {

        public function checkFile($file, array $config) {

            if (!strlen($config['params'])
                return true;

            $extension = preg_match('/\.([^\.]*)$/', $file, $patt)
                ? $patt[1] : "";
            $extensions = preg_split('/\s+/s', trim($config['params']));

            if (in_array($extension, $extensions))
                return true;
            else
                return "Incorrect document type!";
        }
    }


This code is just for an example and don't do any special. This can be done
without making new type detection class with:

    'types' => array(
        ......
        'docsFolder' => "pdf doc xls",
        ......
    ),

See more in type_img.php and type_mime.php (*img and *mime)