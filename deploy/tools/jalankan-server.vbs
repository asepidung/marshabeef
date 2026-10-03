' Menyalakan server Marsha Beef di latar belakang (jendela tersembunyi).
Set fso = CreateObject("Scripting.FileSystemObject")
Set sh = CreateObject("WScript.Shell")

root = fso.GetParentFolderName(fso.GetParentFolderName(WScript.ScriptFullName))
sh.CurrentDirectory = root

php = root & "\php\php.exe"
If Not fso.FileExists(php) Then php = "php"

' 0 = jendela tersembunyi, False = jangan ditunggu
sh.Run """" & php & """ artisan serve --host=127.0.0.1 --port=8000", 0, False
