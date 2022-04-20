# OpencastPageComponent

This version of the ILIAS plugin for Opencast is operated and developed collaboratively by a community. The University of Bern acts as coordinative maintainer (contact: info.ilub@unibe.ch). In this role, the University of Bern organizes monthly meetings, discusses ideas for further development with the community and refers to existing development companies for the implementation of these ideas.

Introduces a new page component, usable in every context where the page editor can be used.

The page editor action "Insert Opencast Video" offers a table to search videos from the configured Opencast installation (configuration of RepositoryObject plugin "Opencast" will be used). The table will show only videos accessible by the current user.

## Getting Started

### Requirements

* ILIAS 6.x / 7.x

### Installation

Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent
cd Customizing/global/plugins/Services/COPage/PageComponent
git clone https://github.com/opencast-ilias/OpencastPageComponent.git OpencastPageComponent
```
Update, activate and config the plugin in the ILIAS Plugin Administration

## License

This project is licensed under the GPL v3 License
