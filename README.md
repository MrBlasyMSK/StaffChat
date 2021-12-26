# StaffChat
Advance private chat plugins for your staff members
Allows you to create a staff chat between staff members with permissions and chat prefix
If you want to make a video of it, please link back to this repo in your description, you may request your videos to be featured here if it meet reasonable quality

## How to install?
To download compiled PHAR, please click the poggit view button above then scroll down selecting the latest by clicking on Direct for latest version, or you can click on latest release "Direct Download"
Put this in your Plugins, start the server to generate a config file, you may edit the config to suit your needs, scroll down for more info on configuration files

## Usage
To chat into staff chat without prefix or commands use "/sc on" this will put all of your message into staff chat for convenience if you wish to have long conversations "/sc off" when you are done

## Commands:

Commands start with /staffchat alias is /sc

| Command | Info                                                      |
|---------|-----------------------------------------------------------|
| on/off  | Switch current chat mode (chats directly into staff chat) |

## Configs:

| Config Value       | Info                                                      |
|--------------------|-----------------------------------------------------------|
| prefix             | The chat prefix                                           |
| console-attach     | Automatically make console listen to staff chat on start? |
| staffchat-format   | The format of the chat                                    |
| enable-functions   | Enable quick functions for chat                           |
| functions-executor | The symbol for the function ex: !                         |
| announce-state     | Announce when a player enter or leaves the chat           |
| join               | The join message                                          |
| leave              | The leave message                                         |

There's also references inside config file

## Functions:

You can use function by typing !func in staff chat and will be replaced with appropriate text

| Function Name | Usage                                              |
|---------------|----------------------------------------------------|
| !pos          | Replaces it with your x,y,z, world                 |
| !focus        | Replaces !focus with the player you are looking at |
| !near         | Replaces it with the players near of you           |

## Permissions:

| permission node | Info                                   |
|-----------------|----------------------------------------|
| staffchat.read  | Allow players to read chat             |
| staffchat.chat  | Allow players to chat into chat        |
| staffchat.cmd   | Allow players to use staff chat comand |

By default all permission nodes are granted for operators.