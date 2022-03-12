<?php

namespace Test;

class Data
{
    /**
     * Flag that tells the test to compare the full transactions
     * with data from bitcoin-cli getblock output.
     *
     * Since the json output for the current 7 blocks are some ~38mb
     * I have excluded them from git!
     *
     * To run the test set this to true and fetch the json data into
     * `tests/data/[HASH].block`.
     *
     * ```sh
     * # bitcoin datadir / configuration dir.
     * export BTCDATADIR=/some/path/4/bitcoin/data/if/needed
     *
     * bitcoin-cli -datadir=${BTCDATADIR} getblock [HASH] 2 > tests/data/[HASH].block
     * ```
     *
     * @var bool
     */
    public static $compareTxData = false;


    /**
     * blkmain0.dat from main net.
     *
     * @var array<array<mixed>>
     */
    public static $blocks = [
        // 2009-01-03 18:15:05 (0)
        [
            'hash'         => '000000000019d6689c085ae165831e934ff763ae46a2a6c172b3f1b60a8ce26f',
            'ntx'          => 1,
            'ts'           => 1231006505,
            'merkle_root'  => '4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b',
            'prev_block'   => '0000000000000000000000000000000000000000000000000000000000000000',
            'version'      => 1,
            'versionHex'   => '00000001',
            'nonce'        => 2083236893,
            'bits'         => 0x1d00ffff,
            'bitsHex'      => '1d00ffff',
            'strippedsize' => 285,
            'size'         => 285,
            'weight'       => 1140,
            'difficulty'   => 1
        ],
        // 2015-03-10 10:25:56 (346981)
        [
            'hash'         => '000000000000000013daeb90ccae964ef668de938d3723215f80e850b97f7c94',
            'ntx'          => 154,
            'ts'           => 1425983156,
            'merkle_root'  => '82a34ac50aaefd4a6b22438dcab7f433468443d83267e33b8aa5cd4c61bb38fd',
            'prev_block'   => '00000000000000000b86ec12dd9d8bd96b4a6ec3f234d97519a8aae4be4be3b4',
            'version'      => 2,
            'versionHex'   => '00000002',
            'nonce'        => 4123100846,
            'bits'         => 0x18172ec0,
            'bitsHex'      => '18172ec0',
            'strippedsize' => 125043,
            'size'         => 125043,
            'weight'       => 500172,
            // 'difficulty'   => 47427554950.6483,
            // for the tests we remove presistion or it will fail
            //  by one decimal to many or to little.
            'difficulty'   => 47427554950,
        ],
        // 2015-03-10 09:47:04 (346977)
        [
            'hash'         => '000000000000000009e54a57139c4d316e51773c457921bdce3fdf22ebd89042',
            'ntx'          => 105,
            'ts'           => 1425980824,
            'merkle_root'  => '8f398997ceae860dabcad34d42a5a7f9f5218efbce1015a2270e9d5353fec637',
            'prev_block'   => '0000000000000000141e12fe904db0d902477c941d8aad78b0a5adce6a75c3e3',
            'version'      => 2,
            'versionHex'   => '00000002',
            'nonce'        => 1021627021,
            'bits'         => 0x18172ec0,
            'bitsHex'      => '18172ec0',
            'strippedsize' => 51277,
            'size'         => 51277,
            'weight'       => 205108,
            // 'difficulty'   => 47427554950.6483,
            // for the tests we remove presistion or it will fail
            //  by one decimal to many or to little.
            'difficulty'   => 47427554950,
        ],
        // 2015-03-10 10:20:49 (346979)
        [
            'hash'         => '0000000000000000013e98ea1c829454fc557c0bc296eb802e24a972de9b0699',
            'ntx'          => 695,
            'ts'           => 1425982849,
            'merkle_root'  => 'ed2ca9582ce8b736048e372488b00074358926739c79639ac22262817435eaa5',
            'prev_block'   => '000000000000000010e4272f91675ab2aeb7b33f6c665e4f17930267a2cd74ca',
            'version'      => 2,
            'versionHex'   => '00000002',
            'nonce'        => 2765568272,
            'bits'         => 0x18172ec0,
            'bitsHex'      => '18172ec0',
            'strippedsize' => 749181,
            'size'         => 749181,
            'weight'       => 2996724,
            // 'difficulty'   => 47427554950.6483,
            // for the tests we remove presistion or it will fail
            //  by one decimal to many or to little.
            'difficulty'   => 47427554950,
        ],
        // 2021-03-10 09:41:18 (673988)
        [
            'hash'         => '000000000000000000014ec65b6f82d1e3ba22d10cc6683c800e4cd2816946a2',
            'ntx'          => 1457,
            'ts'           => 1615369278,
            'merkle_root'  => 'c2c3e3d1b164f4984d52515491184c859c18e49ef82c125209f7b1deae8d36da',
            'prev_block'   => '0000000000000000000ca38e39e7871196be10f5200741f886bb7e043b9efd0e',
            'version'      => 536887296,
            'versionHex'   => '20004000',
            'nonce'        => 2924940114,
            'bits'         => 0x170d1f8c,
            'bitsHex'      => '170d1f8c',
            'strippedsize' => 864441,
            'size'         => 1405317,
            'weight'       => 3998640,
            // 'difficulty'   => 21448277761059.71
            // for the tests we remove presistion or it will fail
            //  by one decimal to many or to little.
            'difficulty'   => 21448277761059
        ],
        // 2021-03-10 09:18:46 (673985)
        [
            'hash'         => '0000000000000000000c29bbeec1e0fd9cfcc51f78eec01dc49eb389a99e887e',
            'ntx'          => 1620,
            'ts'           => 1615367926,
            'merkle_root'  => 'cc6166531e29892750e85af62e2cf3d46f119a5a8e297122400c20ef945ef394',
            'prev_block'   => '0000000000000000000617bc38bff5a87b44277cc8b098d9d56ec9875b97ae59',
            'version'      => 1073676288,
            'versionHex'   => '3fff0000',
            'nonce'        => 218197946,
            'bits'         => 0x170d1f8c,
            'bitsHex'      => '170d1f8c',
            'strippedsize' => 833434,
            'size'         => 1499236,
            'weight'       => 3999538,
            // 'difficulty'   => 21448277761059.71
            // for the tests we remove presistion or it will fail
            //  by one decimal to many or to little.
            'difficulty'   => 21448277761059
        ],
        // 2022-03-10 11:01:09 (726693)
        [
            'hash'         => '00000000000000000004e6c1eec8281d333da8f893400125132dd60c5ff49fa7',
            'ntx'          => 2613,
            'ts'           => 1646910069,
            'merkle_root'  => '6cf035da9ade9f1b44d095e8c55549a996256a9ef4470e0348081d931227b8c4',
            'prev_block'   => '00000000000000000003125e52cbd8f5464529176c141d002a4377bd86b967ba',
            'version'      => 1073676292,
            'versionHex'   => '3fff0004',
            'nonce'        => 1603723681,
            'bits'         => 0x170a3773,
            'bitsHex'      => '170a3773',
            'strippedsize' => 815848,
            'size'         => 1489941,
            'weight'       => 3937485,
            // 'difficulty'   => 27550332084343.84
            // for the tests we remove presistion or it will fail
            //  by one decimal to many or to little.
            'difficulty'   => 27550332084343
        ]
    ];
}
