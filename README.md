# OpencastPageComponent

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
git clone https://github.com/studer-raimann/OpencastPageComponent.git OpencastPageComponent
```
Update, activate and config the plugin in the ILIAS Plugin Administration

## Authors

This is an OpenSource project by studer + raimann ag (https://studer-raimann.ch)

## License

This project is licensed under the GPL v3 License 

### ILIAS Plugin SLA

We love and live the philosophy of Open Source Software! Most of our developments, which we develop on behalf of customers or on our own account, are publicly available free of charge to all interested parties at https://github.com/studer-raimann.

Do you use one of our plugins professionally? Secure the timely availability of this plugin for the upcoming ILIAS versions via SLA. Please inform yourself under https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Please note that we only guarantee support and release maintenance for institutions that sign a SLA.
