# COS20031 Archery Scoring System
Database Design Project Software Development Major Work

A web-based application for a recorder usecase of managing scoring per archery competitions and rounds. This system allows archers to record their scores during competitions while automatically handling round selection based on archer classification and equipment type.

## Features

- Archer selection and management
- Equipment type selection 
- Competition and round selection based on archer classification
- Dynamic round loading based on archer age, gender and equipment
- Real-time score entry with numerical pad
- Score validation and sorting
- Progress tracking through competition ends
- Mobile-responsive design

## Technical Stack

- PHP used for the majority of the code
- MySQL/MariaDB for access to database
- jQuery used for AJAX implementation
- HTML/CSS for displaying frontend

## File Structure

- `index.php` - Main page for the UI
- `submit.php` - Score entering for selected archer
- `fetch_rounds.php` - makes dynamic selection for round based on index.php entries
- `connect.php` - Connects to MariaDB database
- `style.css` - Styling for all files

## Setup

1. Correctly configure database connection in `connect.php`
2. Make sure the following tables exist in the database with the entitities as defined below:
   - Archer
   - Equipment
   - Competition
   - CompRound
   - RoundRange
   - Target
   - Category
   - Class
   - Score
   - ScoreArrow

## Usage

1. Use a terminal window to navigate to the files folder housing all the files of this project and use `php -S localhost:8000` to access the site on port 8000.
2. Select archer from dropdown menu
3. Choose equipment type
4. Select competition
5. Available rounds will load automatically based on archer classification
6. Enter scores using the numerical pad interface, scores get resorted in descending order based on value, but initial order is saved
7. Submit scores for each end
8. Progress through all ends until round completion

## Database Table & Entity Requirements

The following tables, with the outlined attributes should exist in order to get the application running:
- `Archer` (archerId, firstName, lastName, gender, age)
- `Equipment` (equipmentId, equipmentName)
- `Competition` (competitionId, competitionName)
- `Class` (classId, className) - For age/gender classifications
- `Category` (categoryId, classId, equipmentId)
- `CompRound` (roundNo, roundName)
- `RoundRange` (rangeId, roundNo, targetId, endNo)
- `Target` (targetId, distance, targetFace)
- `Score` (scoreId, archerId, roundNo, competitionId, recorderId, totalScore)
- `ScoreArrow` (scoreId, rangeId, arrowNo, scoreNo)
