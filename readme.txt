rsslounge aggregator
http://rsslounge.aditu.de
tobias.zeising@aditu.de
Version 1.7

-------
english
-------

INSTALLATION

1. upload all files of this folder (IMPORTANT: also upload the invisible .htaccess files)
2. make the directories config, data/cache, data/favicons, data/logs, data/thumbnails, public/javascript and public/stylesheets writeable
3. open rsslounge in your browser and follow install instructions

For updating rsslounge feeds via cronjob point your cronjob to open 
    http://<rsslounge url>/update/silent
via wget or curl.

----

UPDATE

1. backup your database and your "data" folder
2. (IMPORTANT: don't delete the "data" folder) delete all old files and folders (including "config") excluding the folder "data"
3. upload all new files and folders excluding the data folder (IMPORTANT: also upload the invisible .htaccess files)
4. Clean your browser cache
5. open rsslounge in your browser and follow the install instructions
6. use your old database connection, the new rsslounge version will update the old database automatically



-------
deutsch
-------

INSTALLATION

1. lade alle Dateien dieses Ordners hoch (WICHTIG: auch die unsichtbaren .htaccess Dateien hochladen)
2. setze die Schreibrechte für die Verzeichnisse config, data/cache, data/favicons, data/logs, data/thumbnails, public/javascript und public/stylesheets
3. öffne rsslounge im Browser und folge den weiteren Anweisungen

Um die Feeds von rsslounge mittels Cronjob zu aktualisieren, einfach einen cronjob anlegen, der die URL
    http://<rsslounge url>/update/silent
mittels wget oder curl aufruft.


----

UPDATE

1. die Datenbank sowie den "data" Ordner sichern
2. (WICHTIG: nicht den "data" Ordner löschen) alle alten Dateien und Ordnern (einschließlich "config") aber ohne dem Ordner "data" löschen
3. alle neuen Dateien und Ordner hochladen (ausgenommen dem "data" Ordner) (WICHTIG: auch die unsichtbaren .htaccess Dateien hochladen)
4. Leere den Cache des Browsers
5. rsslounge im Browser öffnen und den Anweisungen folgen
6. die alte Datenbank für die neue rsslounge Version verwenden (die alte Datenbank wird automatisch aktualisiert)

