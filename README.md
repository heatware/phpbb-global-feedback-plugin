Official HeatWare Plugin for phpBB
======
Since 1999, [HeatWare](http://wwww.heatware.com) has provided free use of its user feedback system, enabling forum users to Buy/Sell/Trade with confidence. We have officially released a phpBB plugin that allows forums to display user feedback statistics from HeatWare in the user's forum profile. HeatWare is a ***global*** feedback system, therefore there is no need to build reputation on EACH forum you visit.

## Compatibility 
* **Supported:** phpBB >=3.1.0
* **Not Supported:** phpBB 3.0.x

## Download
*

## Installation
* Download the latest release and unzip the package in the ext/ folder of your phpBB installation
* Enable the extension in the Customize -> Manage Extensions page

## Setup
* You will now need to navigate to the Extensions tab. Under HeatWare -> Integration Settings you will need to set the API key you received from heatware.support(at)gmail(dot)com
* Also under Integration Settings you have the option to adjust the synchronization frequency and to globally enable feedback for all users (default).

**Where are the feedback stats displayed?**
* When a user posts a message, the Positive/Negative/Neutral feedback count will be displayed under the user's contact information

![phpBB HeatWare Plugin](http://i.imgur.com/63i5wVD.jpg "phpBB HeatWare Plugin")

## Implementation / Design
* Whenever the sync task runs it will attempt to update HeatWare information for all users.
* If the HeatWare ID is zero for a user the sync will perform a lookup using the user's email address. If a user ID is found it will update the database.
* For every user with a valid HeatWare ID the sync will update the account status and feedback numbers.
* Any errors are logged in the "Error log" in the ACP.

## Contributors
I am looking for the community to help with bug fixes, feature development, and testing compatibility with various phpBB versions.

## Versions
### 1.0.0
* Initial release

## Contact
#### heat23
* Homepage: http://www.heatware.com/u/2 
* E-mail: heatware.support(at)gmail(dot)com 