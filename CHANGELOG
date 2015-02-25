# Release 0.3.3

- 661120f Version Bump.
- 9bfa1af Merge branch 'feature/updater-basic' into develop
- 83677a3 Updated ...
- d2dd1f3 Create updater, closes #29
- 36c1638 Fixed indetation ...
- 3ded070 Fixes #59
- 8a1ce2d Merge branch 'feature/proper-logging' into develop
- 7a0112b Added `DateTimeFileLogger` to enable proper loggin, closes #56
- 44ffb64 Reworked default `phpFastCache` options to be more consistent.
- ea6d0d9 Fixed version property location within json.
- 26859e9 Remove obsolete variable. Closes #57
- 63ed2d3 Version Bump.

# Release 0.3.2

- 469e23b Merge branch 'release/0.3.2'
- 4bf3395 Merge branch 'feature/db-setup-fix' into develop
- 96f360b Some further rework of the install process.
- f338def Mostly: rework the database imports.
- 5b63757 Implement import method. This way an upcoming updater does not need to implement this functionality of its own.
- 8fcf362 Holding pdo object persistent and fixed init command.
- eb0e2a4 Changed back to persistent pdo mode.
- 55d3a10 Changes due to Db handling rework.
- 434e041 Reworked Db handling.
- 94e3ed2 aaand undone again, as md skills yet seem to suck. grrr...
- dc60c70 Renamed to fully support Markdown on Github.
- e88c02a Merge branch 'release/0.3.1' into develop

# Release 0.3.1

- d3a91aa Version Bump.
- 6129354 Merge branch 'release/0.3.0' into develop

# Release 0.3.0

- d1f663d Merge branch 'develop' of github.com:ta2edchimp/battlereporter into develop
- 3077270 Added clickable `footage` icon to "pov-pilots" on BattleReport. Closes #47
- d7a8d0f Added clickable `footage` icon to "pov-pilots".
- fce3e33 Updated schema for `cyno` feature.
- baf4b52 Reincluded detection of already imported kills.
- c824fa1 Mark cynos, closes #54
- a5787e2 Reworked 3rd-party display on Index.
- cc0ef75 Fix #53
- bb8cccb Fix #55
- 4fae4e2 Fix #52

# Release 0.2.8

- a10d269 Merge branch 'release/0.2.8' - fixes #36 - fixes #44
- 6a50251 Version Bump.
- 18d131e Merge branch 'feature/kb-fetch-newday' into develop Fixes #44
- 0c8f514 Changed pattern for check of `timespan` user input to work the new way which allows for inputs like `15-2-9 1:23 - 2:34` instead of `2015-02-09 01:23 - 02:34`.
- 382b2c1 Changed parsing of start and end time from user `timespan` string.
- 02ecc06 Fix minor parsing error.
- 07cf3df Merge branch 'feature/br-ship-grouping' into develop
- e40ee37 Implement a better way to sort combatants within a battle party.
- 79a979d Fill BR Ship Groups tables with values for `Logitics`, `Ewar` and `Capitals`.
- 3862cbe Schema for BR Ship Groups.

# Release 0.2.7

- 30f7402 Merge branch 'release/0.2.7'
- 3c3bce2 Merge branch 'hotfix/0.2.6-hotfix-48' into develop
- b73368f Merge branch 'hotfix/0.2.6-hotfix-48'
- 141d0e4 Fixes #48
- 1ea6a8f Merge branch 'hotfix/v0.2.6-hotfix-46' into develop
- 5888ed4 Merge branch 'hotfix/v0.2.6-hotfix-46'
- f0f0340 Fixes #46
- d3b91f3 Merge branch 'master' into develop
- 83ce5e2 Merge branch 'hotfix/issue-45' into develop
- d6edc83 Merge branch 'hotfix/issue-45'
- 05fdcb8 Fix #45
- 52b2569 Merge branch 'release/0.2.6' into develop

# Release 0.2.6

- f731d34 Merge branch 'release/0.2.6'
- 06acb17 ID fields are now hidden, as they should be.
- 5b3de4d Redirect on successfull saving reactivated.
- 6fbfa9f Updated database schemas.
- 7d2d7b1 Merge branch 'feature/multiple-pov-footage' into develop
- 2d4d573 Assign combatants from battle report to connected videos, closes #34
- 2fd65d9 Reorder added videos.
- d12cdc4 Add possibility to add multiple videos to a battle report.

# Release 0.2.5

- db2c3f5 Enable direct linking to comments and footage of battle reports, closes #40
- 2c4b359 Changed response to dismissing the editing of a battle report.
- 303ba51 Propulate number of pilots per team in `brBattles`, simplifies `Index` query and correctscombatant count, fixes #42
- 63c25d2 Support YouTube short urls, closes #37
- c8c9e7c Test i query returned any value at all, fixes #38
- 70bb888 Fix #39 corporation / alliance update on EVE SSO login
- 45c71bc mini bugfix ...
- 4b2ab4b Changed path concatenation
- 2331413 Activate caching on productive environments to speed things up a bit.
- 8421d4e `time_ago` Twig filter was missing the case for < 1d but ≥ 1 day.

# Release 0.2.4

- 8404aca Use mini `SLInsight` widget.
- 0be94af Added hint for videos, included proper tooltip for `hasFootage` and `hasComments` icons. Closes #33
- ad0ac88 Set `erusev/parsedown` package requirement from `dev-master` to (final) `1.*`.
- 5c47258 Make use of class `Admin` (where needed), to prevent cluttering the global namespace with admin-functions.
- cbf163e Use `Slim::getInstance()` instead of `global`.
- d331bd0 Use `Db::getInstance()` instead of `global`.
- ed870a8 Register db-accessor as globally accessible instance, to prevent use of `global`.
- 3d732ef Implemented ability to create a life-cycle dependent, global instance of the db accessor, if needed.
- 430f698 Code cleanup & prevent `global`.
- eca035c Use strict comparison where possible.
- 12a8f65 Use strict comparison where possible.
- 3e0a27e Added nl at eof, to comply with code quality policies.
- a0e59d8 Throw proper exception instead of the previously used `die()` quirks.
- e133756 Now, correctly redirect the `Slim` way ...
- 5fa3f2b Updated.
- a214712 Updated.
- 426d4a2 Removed.
- 3603a0b Exclude `.htaccess` from vcs, as this is rather installation specific and should not be distributed.
- 601fecc Added license field to comply with composer schema.
- 050ab4e Code cleanup.
- 5b6ca38 Removed unnecessary call of `exit()`, to not imply unreachable code ...
- 4e09432 Code cleanup.
- e759451 Removed unused variable.
- d823144 Removed unused method stub.
- 3458dec Removed unused variables.
- 0d6b3d2 Use of type hinting, where possible.
- bf21410 Added project name and a discription.
- 9f2f507 meh ...
- 47c5a23 Added default `favicon`
- f7f563d Updated for removed `DAO.php` include ...
- e0a1243 Removed (unused).
- ff834f1 Implemented comments, closes #23
- 54a89ec Included ci medal (if earned sometime).
- 0295899 Added missing semicolon
- 6626e1a Meh ...
- 94064c8 A working errors page, eventually
- eda5c21 Redirect to referer if successfully logged in via SSO.

# Release 0.2.3

- 4c221ae Corp and Alli handling improved, closes #32
- c3b3b88 Add Videos to BattleReports

# Release 0.2.2

- 6ab5171 Fallback for missing `$_SERVER["REQUEST_SCHEME"]` on some servers running PHP.
- c83757c Backwards comp.
- 1c5c382 Hide survived pods, closes #16
- 62ccad4 Minimum layout improvement.
- f0595f3 Layout improvements for logged in users.
- 675e25b Updated with EVE-O SSO setup instructions
- 50584ff Updated for EVE-O SSO credentials

# Release 0.2.1

- aa442b9 Prevent unnecessary user acount creation.
- 0ee9039 Clarified variable name ...

# Release 0.2

- 188307c Added EVE-O SSO, closes #6
- 4ec2c5c Bugfix for deleted battle reports.
- ed94e2d BR List made more mobile friendly, according to #14
- a3b8af6 Minor layout change.
- 70e4222 Easy access for admins to their tool(s).
- 236cdd3 Bugfixes for db->query return values.
- 2af191c Closes #27
- e650b79 A smaller one.
- 935ac31 Added SDE tables, as they are mandatory for BattleReporters operation.
- a310744 Force warnings into error.
- cdf5768 Tweaked the caching lib.
- a816bfc Meh... nvm
- 2ced918 Closes #8
- 963851f Implement a basic installer, see #8
- 64dfb8e Updated phpFastCache dependency.
- 3cf354c Delete BattleReports, closes #11
- 587d906 Fixes bug that denied permitted users to display unpublished/edit their own published  battle reports.
- 39dbe8e Closes #13
- 3906424 Added `/info`, closes #15
- f03d47a Added Parsedown for automatic MarkDown parsing and displaying within a `twigged` page.
- d5647e3 Link survived combatants too, closes #28
- 5c28a77 Restrict functions to adequate user permissions, closes #20
- 5af68cc Fixes `creatorUserName` column not existing bug.
- f3e8ffe Closes #21
- 087fa5c Track id of user creating the battle report.
- e936ba9 minor update
- 44450b4 Timeline clickable, closes #17
- b5e1c3c Ability to delete manually added combatants, closes #24
- c3d9753 Moar responsiveness for the navbar.
- cbb19f0 Fix #19
- 6bf8d5b Slight restyling of the overview table links
- e22b7cb Unify date/time rendering, assure that manually added, but eventually deleted combatants are NOT put into the database.
- 4ea885e Force timezone to default to "UTC", fixes #25
- 97a5708 Minor layouting fix
- f348b19 Updated for release 0.1.1
- 7c46f37 PHP 5.4 compatibility.
- 314b493 Test output removed.

# Release 0.1

- f14302b Specify corp- and alliance names on manually added combatants, closes #4.
- d0ffac5 Adding Combatants to BattleReport, closes #2
- 4d7a4e2 Inverted order in Battle Timeline.
- adefb49 Unpublish BattleReports, closes #10
- 129e11a Reject creation of duplicate BattleReports, fixes #12
- 8bcdad1 Battle timeline implemented, closes #5
- 1a089c8 User management, closes #1
- 9c260b0 Empty default config
- 1572592 Exclude test page
- c70db06 Basic overview implemented.
- 52e0232 Corrected access rights.
- e1a45c3 Implemented "Changes" to edit and save battle reports.
- 47b1dff Save prepared and display saved BRs
- ba635e2 Loading BRs from database
- b5c5271 Preparation for saving changes and editing existing battle reports.
- 836753d Save records to database
- 7c3088d Moar functionality
- b3a019c DB tables
- 685284d Small fix and removal of test outputs.
- 479c04d Removed emulated fetch (zKB was down all day D:)
- 5e39d63 Editing fetched battles parties.
- 65371a8 Fix: Efficiency calculation for empty/unused team(s).
- b742fb9 Removed test route
- b28c9ae Fetching kills and setting up a new battle report
- 2ff79c5 New includes and their initialization
- d5becde AJAX: query solar systems
- f6c3284 Fetching and processing kills, resulting in battle report preparations
- 46431eb Query db for item types
- 34fcbef Query db for solar systems
- dfdf3e0 Tpl. updated for BR creation
- 2c3c2e1 jQuery AutoComplete
- 85ae76c Reset parameters after a query.
- baa5314 wip
- f3e875b Default theme.
- 063cb23 moar functionality ...
- f2800d5 Basic session & user handling
- 12493a1 Added proper caching
- 5930030 Updated requirements.
- acbdbad phpFastCache added
- 28f0dd0 Apache Rewrite Config for Slim
- 38b3657 Basic fetching by url
- 1866b31 Updated setup instructions
- 89d2f81 Oops.
- 58c0c75 Oops.
- 4ac0910 Separated Configuration, Initialization and Routing Setup.
- b7d936c Updated Requirements
- 053564f DB Access
- d912904 Basic Setup Guide
