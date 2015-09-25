# Release 0.5.0

- 30aea83 Modified default theme to better fit mobile display (≤ 640 px width). Closes #14
- be2a87d Added `br has an aar` hint icon on overview list. Closes #71
- 4b7dfff Strict comparison.
- 670498f Added `Coveralls.io` badge, because BADGES!!
- 5d888a2 Moved `atoum/atoum` from general requirements to development requirements (not necessary when only using BattleReporter). Closes #83
- 89a5063 Added `.atoum.php` to create proper reports about the tests and deliver them to `coveralls.io`.
- 3a5b78d Added update information when there are newer releases available.
- abeacce Implemented method to compare versions.
- 0b3f5dc Implemented utility method to parse versions from strings.
- 0787fa6 Added test for `Fetcher\Curl::fetch`
- b74b3eb Removed obsolete debug logging.
- 0de8590 Removed obsolete debug logging.
- 91a7c8b Prevent unnecessary curl output to stdout.
- 37f7d64 Removed unnecessary `tag` badge.
- 257399f Partial testing of classes/Utils.php
- e3f1696 Added documentation.
- fc2cde5 Fixed indentation.
- a496605 Added documentation.
- 0415bcb Completed documentation.
- c148d4b Added documentation.
- 59af016 Added partial tests for classes/KBFetch.php
- 85f8e96 Added partial documentation.
- ebf6b30 Tests for classes/Db.php
- 53314c1 classes/SolarSystem.php fully covered by unit tests.
- 0296922 Badges! Removed old sli badge.
- 0846672 Updated for code quality analyses, tests, etc.
- e0efa30 Use dedicated automatic installation procedure.
- 6a5821e Updated for automation.
- 6b160f9 Implement tests ...
- a557f95 Bugfix for falsely interpreted numerical value in php strict mode.
- d430757 Merge branch 'release/0.4.7' into develop

# Release 0.4.7

- 44f63fe Versionbump.
- acc18bc Corrected namespaced class imports.
- 8e450fd Do not suppress errors on `file_get_contents`, as we are even specifically change the php error settings to be able to catch possible errors.
- 1a5374c Cleared up namespace uses.
- 8959698 Auto-caching not implemented, therefore to reduce dead code, removed corresponding flags.
- fc15ce4 ... and a bugfix for previously undefined variables from #2c5fc8e
- 2c5fc8e Make use of existing information when receiving errors.
- 8d02af5 Deleted commented out code.
- 08b6b7d Changes due to modifications made to the Fetcher implementations.
- 20290e8 Fetcher implementations now inherit from `FetcherBase` class. Duplicate code removed.
- 01f3e7b Changed base implementation of `Fetcher` into `FetcherInterface` and a `FetcherBase` class, to store otherwise duplicate code of the concrete Fetcher implementations of `File` and `Curl`.
- 94910e8 Merge branch 'release/0.4.6' into develop

# Release 0.4.6

- c09d3fc DB Updated for `Galatea`.
- f4ec4c1 Adding multiple combatants at once implemented, closes #79
- 533992e Merge branch 'release/0.4.5' into develop

# Release 0.4.5

- 3063938 Fixes #80
- 4ef37a0 Merge branch 'feature/curl-alternative' into develop
- a2a81bc Make use of new fetcher.
- 1e9a34c Setup & initialize fetcher.
- 81b8546 Include new fetcher.
- 5f81a71 Impl. alternative to curl, closes #77
- 54d7d62 Fixed variable to correctly point towards the current Slim instance.
- fb195a6 Merge branch 'release/0.4.4' into develop

# Release 0.4.4

- 064fe64 "toJSON" Rework.
- a101f5b Merge branch 'release/0.4.3' into develop

# Release 0.4.3

- 5a1c7ed Credited Bitslix in "About" infomation ... ;P
- 1a8277f Fetch statement slightly optimized, closes #78
- 081e35c Merge branch 'release/0.4.2' into develop

# Release 0.4.2

- 444fe09 Added use of `endPoint` to API calls due to changes in zKB/EveKill API "changes". zKillboard API fetching not working atm.
- 656112a Closes #69
- 9ad932d Fix for entities not found by name, closes #75
- da8b8c7 PhealExceptions caught properly, closes #74
- 32113e4 Improved debug config.
- 7d48596 Prevent combatant doublettes.
- 3e7fe20 Corrected partial double setup of ewar group (wtf did i do there?).
- beb42cf Whitespace tweaking.
- 2bdf3ea Tweaked popover layout.
- 272a5d3 Updated general page footer.
- 8924f41 Separated battle footage into `components` sub directory.
- 40613f4 Allow embedded videos to expand to fullscreen.
- e65d34e Centralized and unified additional css.
- 3349593 Centralized and unified additional css.
- 349891b Whitespace clean up.
- 4518371 Whitespace clean up.
- ec3896b Whitespace clean up.
- dce7153 Whitespace clean up.
- 4f58806 Whitespace clean up.
- afdbc6d Fixed bug caused by uncautious script exclusion.
- 3897c9b Manual minifying of js block.
- 4cc0480 Provide minified editor js, shrunken to ~60% of original file size.
- 479cbb4 Cleansed code from unnecessary 4-spaces indentations, replaced them by tabs.
- b175c72 Extracted editor js from templates.
- 8fb23ef Correct identification of direct messages.
- e891b9f Changed layout of messages sent to `Slack`.
- ec2df5e Merge branch 'release/0.4.1' into develop

# Release 0.4.1

- b8ade90 Fixed log output.
- f50c038 Updated `phpFastCache` dependency, removed unnecessary default chmod value.
- d1f3a89 Proper mode configuration for `Slim`.
- 38f8818 Merge branch 'feature/preview-descriptions' into develop
- 86c5998 Added robots rules.
- 91d12fe Added keywords, default title and description meta tags.
- 68f87db Provide proper preview information.
- 21ca5e8 Add descriptive meta tags for Open Graph and Twitter Cards, to deliver proper previews.
- 9bde7a0 Fixed comparison, closes #68
- cbfdf60 Version Bump.
- 39432a2 Merge branch 'release/0.4' into develop

# Release 0.4

- ef45de9 Version Bump.
- 3bf28a6 Added update hint for non-updated pre `1.0` config values.
- 4195fcd Add nl at eof, closes #65
- 9b12e4f Merge branch 'feature/modify-battle-timespan' into develop
- 496976b Adjust timespans and refetch kill mails. Closes #49
- 8b658d0 Merge branch 'release/0.3.8' into develop
- 5c25c99 Functionality rearrangement to support appending of events besides exclusive report creation.
- 2d9fa70 Reordered methods more logically.

# Release 0.3.8

- 64c9e8e Merge branch 'feature/battle-summary' into develop
- 4b71bd5 Markdown explanation added, closes #26
- 3b3e1d1 Display summaries; show a preview and expandable rest on longer summaries.
- 8c0d789 Merge branch 'hotfix/0.3.7' into develop
- 5c6ffad Implement battle summary.
- 92b1da6 Foundation for `continue reading` expander filter.
- 62ad03b Implement a markdown filter.
- 3e0e955 Include `battle summary` column.
- fd2e14b Show # of videos in and comments on a br, closes #61.
- b9774cb Of course, comments should not be teased on index, instead of footage, when `comments` are disabled.
- ebdee60 Added basic instructions for the use of the new `Slack` Slash Command.
- ffb3338 Add fallback for Slack messages to be shown in notifications.
- 2e6ecdf Merge branch 'release/0.3.6' into develop

# Release 0.3.7

- 032c625 Fix #62
- 01bebbb Fix #63
- c2f454b Fix #64

# Release 0.3.6

- bcb840d Merge branch 'feature/slack-integration' into develop
- 117d71a Updated for instructions on `Slack` integration.
- 2345f4d Implement `Slack` support (`Slash Command` and `Incoming WebHook`).
- 1713d2e Basic Slash Command support for `Slack`.
- e0c50e5 Basic posting to `Slack`.
- 149b42e Basic Slack Webhook/Slash Command Implementation
- 08ca91c Merge branch 'release/0.3.5' into develop

# Release 0.3.5

- 5c800ec Merge branch 'feature/corproles-accountpermissions' into develop
- ada9f74 Updated for new corp roles feature.
- 840eef5 Update to new roles per API feature.
- 738d1af Update user permissions by role in corp.
- 51971dd db schema for user roles besides normal and admin.
- 38add76 db schema for storing api keys.
- 40a445e Merge branch 'release/0.3.4' into develop

# Release 0.3.4

- d8272d9 Change suffix to support Markdown detection on this file.
- edd2573 Changed to Markdown.
- 927910b Set phpFastCache dependency to fixed version, closes #58
- 598c991 Remove version field, as it is recommended to leave it out.
- b30b180 Added check for required folders, create them if necessary.
- 617754a Corrected some line-breaks ...
- 468d97c Bugfix .. stupid me.
- c7c76c3 Fixed version info and Changelog ...
- 661120f Version Bump.

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
