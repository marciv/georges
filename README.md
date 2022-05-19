# George

## Prerequisites

First, check if the George folder is in the root:

```
C:\\Wamp\www\1root\library\
```

Then in the LP:

- Include master-headerV4(In Futur master-headerV2) in the index.php
- Include in the js/general.js where the form is sent

```
let event = new CustomEvent('form-sended', {
bubbles: true,
cancelable: false
});

document.dispatchEvent(event);
```

## Running the tests

- Start : Go to the url of the root of the folder

```
https://nomdomaine.fr/library/George/
```

### ADD ABTest

Fill in URL inputs without adding parameters

```
URL conversion => https://www.je-renove.net/pho/lan/06/

Discovery Rate => 0.20

URL variation => https://www.je-renove.net/pho/lan/16/
```

Click in the Button "Start AB Test"
