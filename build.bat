:: Delete old data
del massengeschmack.host

:: get recent version of the provider base class
copy /Y ..\provider-boilerplate\src\provider.php provider.php

:: create the .tar.gz
7z a -ttar -so massengeschmack INFO massengeschmack.php provider.php | 7z a -si -tgzip massengeschmack.host

del provider.php