Simple memorizer is a lightweight web application that helps to efficiently memorize any question-answer sets.

## How it works

- user registers and logs in via oAuth2 services: Facebook or Google
- any user can create arbitrary number of courses, course is a question wrapper
- any course can contain arbitrary number of questions provided by the user, question is a key-value pair
- while working in run mode system ask questions from particular course
- user clicks "Good" button if they know the answer, "Bad" if not or "Show answer" to show the answer
- there is no need to provide the answer in any way, user doesn't have to prove he knows it
- the more times user will mark particular questions as well known, the less they will be asked
- analogously, questions with more wrong answers will be asked more often

## Points rules

- default question points value is 10
- every good answer decreases points by 1
- every wrong answer increases points by 1
- questions with just one point are considered as well known
- course average points value is recomputed at every points change

Live demo is available on http://memo.trzebinski.info/

Screenshots http://bit.ly/OqaVRV

## System requirements

- PHP >= 5.3
- MySQL >= 5.5

## User requirements

- modern web browser
- Facebook or Google account

## Setup

0. Move application/config_default/ to application/config/
1. Create MySQL database and import its structure from /simple-memorizer.sql file
2. Change database credentials in /application/config/database.php
3. Change oAuth services credentials in application/config/config.php
4. Set $config['encryption_key'] = '' to some random hash in application/config/config.php
5. In production environment set define('ENVIRONMENT', 'production'); in /index.php
6. System is ready to use

## To do

- csv import/export of courses/questions
- reversed course mode (v == a && a == q)
- REST API for external client applications
- predefined/public courses