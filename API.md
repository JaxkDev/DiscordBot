# DiscordBot API

Would love to have this as a github site, However i don't have the time.

**Version 2.0.x documentation.**

---

## API Structure
Firstly a quick few notes about the API and what it covers.

+ These namespaces are **INTERNAL ONLY**:
    + `JaxkDev\DiscordBot\Bot`
    + `JaxkDev\DiscordBot\Communication`
    + vendor libs, (Not actually available in the main thread)


 + All API Calls can be made through `JaxkDev\DiscordBot\Plugin\Api.php` an instance is available from the plugin instance
(`JaxkDev\DiscordBot\Plugin\Main.php`)


+ All discord data types such as Member, Role, Channel etc
That is all located in the namespace `JaxkDev\DiscordBot\Models` all type info and required data can be found
in the relevant file.


+ All the actual discord data such as servers, roles members can be found in plugin storage anywhere from 
5seconds to 500seconds after the plugin enables.

    + Listen to `JaxkDev\DiscordBot\Plugin\Events\DiscordReady.php` this is emitted when bot is connected
**and** initial data has been received, only use the API and Storage after this event.

---

## API Promises

When you make an API call to do anything discord side, for example send a message to a channel.
It will return something called a `Promise`, And this promise will allow you to know the end result, in this case did
the message send successfully, or did it fail to send?

The reason DiscordBot uses promises is that it cannot return the result 'instantly', as the plugin has to send it to
another thread (discord bot) and then receive the result which is naturally about 3-10 ticks later.

So using a promise we can return an interface that allows you the person sending the request to attach a callback for
when the request is `resolved` or `rejected`.

if its `resolved` it means the request was handled successfully, in this scenario it means message was sent successfully
and a `ApiResolution` will be passed back.

if its `rejected` it means something happened, and it failed to finish the request successfully, in this scenario it means
the message did not get sent, and a `ApiRejection` (exception) will be passed back.

#### API Promise example:

```php
/** @var $api \JaxkDev\DiscordBot\Plugin\Api */
$api = $DiscordBotPluginInstance->getApi();
/** @var $promise \JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface */
$promise = $api->sendMessage(/*Message model from $api->createMessage()*/);

// You could do other things here if necessary
// but be sure to register your callbacks before finishing.

//To handle both resolved and rejected:
$promise->then(function(\JaxkDev\DiscordBot\Plugin\ApiResultion $resolution){
    echo "Resolved !";
    //Yay, it worked and the message was sent successfully.
}, function(\JaxkDev\DiscordBot\Plugin\ApiRejection $rejectedError){
    echo "Rejected :(";
    //Oh no, It failed and $rejectedError can tell you why.
});

//Or handle just resolved:
$promise->then(function(\JaxkDev\DiscordBot\Plugin\ApiResultion $resolution){
    echo "Resolved !";
    //Yay, it worked and the message was sent successfully.
});

//Or handle just rejected:
$promise->otherwise(function(\JaxkDev\DiscordBot\Plugin\ApiRejection $rejectedError){
    echo "Rejected :(";
    //Oh no, It failed and $rejectedError can tell you why.
});
//same as:
$promise->then(null, function(\JaxkDev\DiscordBot\Plugin\ApiRejection $rejectedError){
    echo "Rejected :(";
    //Oh no, It failed and $rejectedError can tell you why.
});

echo "Finished";
```

Please note the order of execution:
1. "Finished"
2. *Server/Plugin continues as normal, for at least 2/3 ticks*
3. "Resolved !" or "Rejected :(" - **Never both**

For more in depth details about the promise interface see `JaxkDev\DiscordBot\Libs\React\Promise\PromiseInterface.php`

For information on the argument types for `$resolution` and `$rejectedError` see below.

---

## API documentation

This section documents every API method, and the possible input, return/resolution, and behaviour.


Notes:
+ `Resolution`
    + Resolution is always a instance of `JaxkDev\DiscordBot\Plugin\ApiResolution`
    + A message (Resolution::getMessage()) is always present.
    + Any optional data (Resolution::getData()) is documented below.


+ `RejectedError`
    + RejectedError is always an instance of `JaxkDev\DiscordBot\Plugin\ApiRejection`
      where details about the rejection as well as the raw DiscordPHP rejection (if applicable) can be found.


+ All API methods documented below can be found in `JaxkDev\DiscordBot\Plugin\Api.php`

--- 

#### Create a message
+ **Signature** - `createMessageModel(TextChannel|User $channel, string $content): ?Message`
+ **Input**
    + `$channel` - A TextChannel model or User model (for DM Message)
    + `$content` - (Text only right now) Content to send.
        + Min length = 1
        + Max length = 2000
+ **Output** - Message model or null.

---

#### Send message
+ **Signature** - `sendMessage(Message $message): PromiseInterface`
+ **Input**
    + `$message` - Message model, see `createMessageModel`.
+ **Output** - PromiseInterface
    + **Resolution::getData()** - Model message with updated values like ID, timestamp etc.
    + **RejectedError** - ApiRejection, rejected when:
        + Message model is invalid.
        + Cannot fetch the server(excluding DM's) or channel.
        + Bot does not have permission to send messages to that channel.

---

#### Kick member
+ **Signature** - `kickMember(Member $member): PromiseInterface`
+ **Input**
    + `$member` - Member model.
+ **Output** - PromiseInterface or null if member cannot be found with ID provided.
    + **Resolution::getData()** - *N/A*
    + **RejectedError** - ApiRejection, Possible rejections:
        + Member model is invalid.
        + Cannot fetch the server.
        + Member cannot be kicked (bot may not have permission)

---

#### Create a ban
+ **Signature** - `createBanModel(Member $member, ?string $reason = null, ?int $daysToDelete = null): ?Ban`
+ **Input**
    + `$member` - A Member model
    + `$reason` - (Optional) Reason for the ban, note this is not sent to the member only used for audit log.
    + `$daysToDelete` - (Optional) Amount of days worth of messages to be deleted that were sent by this member.
        + Min value = 0
        + Max value = 7 (inclusive)
+ **Output** - Ban model or null.

---

#### Ban member
+ **Signature** - `banMember(Ban $ban): PromiseInterface`
+ **Input**
    + `$ban` - Ban model, see `createBan()`.
+ **Output** - PromiseInterface.   
    + **Resolution::getData()** - *N/A*
    + **RejectedError** - ApiRejection, Possible rejections:
        + Ban model is invalid.
        + Cannot fetch the server.
        + Member cannot be banned (bot may not have permission)

---

#### Unban member
+ **Signature** - `unbanMember(Ban $ban): PromiseInterface`
+ **Input**
    + `$ban` - Ban model, can be fetched from storage.
+ **Output** - PromiseInterface.
    + **Resolution::getData()** - *N/A*
    + **RejectedError** - ApiRejection, Possible rejections:
        + Ban model is invalid.
        + Cannot fetch the server.
        + Bot does not have permission to unban members.

TODO More.

---

## Discord api - weird things

Discord does do some fancy weird things a lot like minecraft, so I've listed a few things that are important to note.

`discord gateway/http api v6` The default and current version DiscordBot/DiscordPHP uses.

+ DM Channels
    + Each DM has its own channel however because discord sends a channel create event before any update to a DM such
      as sending a message, pinning etc. we cannot reliably store a DM Channel.


+ Messages
    + Embeds
        + Messages containing embeds send a message update event straight after a create event, even if it has not been updated.
    + Replies
        + I have not looked into this at all (TODO), however I am aware the API to reply to a message is still using the old format. (new format in discord api v8 iirc)
        + New replies may not be handled as a message or not being picked up at all (TODO Check).


+ Server Channels
    + Each server channel has its own pinned messages however discord does not say what message has been pinned/unpinned/deleted so we cannot reliably track/store pins yet.
        + Note, However you can still attempt to pin/unpin a message.


+ Voice Channels
    + Due to largely sized binaries required for different operating systems the bot cannot connect/play music in a VC.

