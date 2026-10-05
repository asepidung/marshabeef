' Menyalakan server Marsha Beef di latar belakang (jendela tersembunyi).
' Memakai "php -S" langsung (satu proses, lebih cepat daripada "php artisan serve").
' Folder kerja HARUS public karena router Laravel (server.php) mencari index.php di folder kerja.
Set fso = CreateObject("Scripting.FileSystemObject")
Set sh = CreateObject("WScript.Shell")

root = fso.GetParentFolderName(fso.GetParentFolderName(WScript.ScriptFullName))
pub = root & "\public"
router = root & "\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"

php = root & "\php\php.exe"
If Not fso.FileExists(php) Then php = "php"

port = sh.ExpandEnvironmentStrings("%MARSHA_PORT%")
If port = "%MARSHA_PORT%" Or port = "" Then port = "8000"

' Jaring pengaman OPcache: bila memori bersama OPcache gagal dipakai (galat ASLR di sebagian PC Windows),
' PHP beralih ke cache berkas alih-alih berhenti. Lihat opcache.file_cache di php\php.ini.
opcacheDir = root & "\storage\framework\opcache"
If Not fso.FolderExists(opcacheDir) Then fso.CreateFolder(opcacheDir)
sh.Environment("Process")("MARSHA_OPCACHE_DIR") = opcacheDir

sh.CurrentDirectory = pub

' 0 = jendela tersembunyi, False = jangan ditunggu
sh.Run """" & php & """ -S 127.0.0.1:" & port & " -t """ & pub & """ """ & router & """", 0, False
