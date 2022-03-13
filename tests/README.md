# Tests
Download the data first, or assemble your own.

### blkmain0.dat

The file is used for general tests an contains the blocks

- 000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f
- 000000000000000013daeb90ccae964ef668de938d3723215f80e850b97f7c94
- 000000000000000009e54a57139c4d316e51773c457921bdce3fdf22ebd89042
- 0000000000000000013e98ea1c829454fc557c0bc296eb802e24a972de9b0699
- 000000000000000000014ec65b6f82d1e3ba22d10cc6683c800e4cd2816946a2
- 0000000000000000000c29bbeec1e0fd9cfcc51f78eec01dc49eb389a99e887e
- 00000000000000000004e6c1eec8281d333da8f893400125132dd60c5ff49fa7

```sh
wget https://cjpgdk.github.io/bitcoin-blk-parser/data/blkmain0.dat -o tests/data/blkmain0.dat
```


### *.block files

They are just outputs from `bitcoi-cli getblock [HASH] 2`, used for doing a full test of the transactions.


** Download the blocks from web **

```sh
#!/bin/bash

BLOCKS="000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f \
       000000000000000013daeb90ccae964ef668de938d3723215f80e850b97f7c94 \
       000000000000000009e54a57139c4d316e51773c457921bdce3fdf22ebd89042 \
       0000000000000000013e98ea1c829454fc557c0bc296eb802e24a972de9b0699 \
       000000000000000000014ec65b6f82d1e3ba22d10cc6683c800e4cd2816946a2 \
       0000000000000000000c29bbeec1e0fd9cfcc51f78eec01dc49eb389a99e887e \
       00000000000000000004e6c1eec8281d333da8f893400125132dd60c5ff49fa7"

for block in ${BLOCKS}; do
    wget https://cjpgdk.github.io/bitcoin-blk-parser/data/${block} -o tests/data/${block}.block
done

```

** Get the blocks bitcoin-cli **
```sh
#!/bin/bash

BLOCKS="000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f \
       000000000000000013daeb90ccae964ef668de938d3723215f80e850b97f7c94 \
       000000000000000009e54a57139c4d316e51773c457921bdce3fdf22ebd89042 \
       0000000000000000013e98ea1c829454fc557c0bc296eb802e24a972de9b0699 \
       000000000000000000014ec65b6f82d1e3ba22d10cc6683c800e4cd2816946a2 \
       0000000000000000000c29bbeec1e0fd9cfcc51f78eec01dc49eb389a99e887e \
       00000000000000000004e6c1eec8281d333da8f893400125132dd60c5ff49fa7"

for block in ${BLOCKS}; do
    bitcoin-cli getblock ${block} 2 > ${block}.block
done

```