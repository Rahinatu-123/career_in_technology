@echo off
echo Creating directory structure...
mkdir app\controllers
mkdir app\models
mkdir views\careers
mkdir views\stories
mkdir views\layouts
mkdir database

echo Moving database files...
move database.sql database\
move api\config.php database\

echo Moving API files...
move api\careers.php app\controllers\CareerController.php
move api\inspiring_stories.php app\controllers\StoryController.php
move api\career_profiles.php app\models\Career.php

echo Moving view files...
move careers.php views\careers\index.php
move career_detail.php views\careers\detail.php

echo Done!
pause 