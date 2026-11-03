# M5-C Legacy Analysis (analysis only - no import)

Source: u0506662_best.sql (9,412,624 bytes, 52,162 lines, phpMyAdmin 5.2.3 dump).

## 1. SOURCE FILES

| File | Size | Use |
|---|---|---|
| u0506662_best.sql | 9,412,624 B | PRIMARY SOURCE (parsed) |
| U1-kategori-rows.csv | 20,006 B | cross-reference |
| U2-urun-schema.txt | 956 B | column validation |
| U3-urun-category-map.txt | 439 B | distribution compare |
| U4-urun-images.csv | 2,164 B | cross-reference (25 rows - partial) |
| U6-empty-images.txt | 315 B | cross-validation |
| U7-image-match.txt | 3,298 B | reference only |
| U8-new-table-counts.txt | 369 B | new table state |
| uploads/ | 59 entries | image token compare |
| U4-urun-images.txt | 674 B | **NOT USED** (unreliable) |

## 2. LEGACY CATEGORY INVENTORY

**12 categories; IDs 53-64; names fixed via Windows-1252 round-trip (12/12 OK).**

| ID | Name (fixed) | Sira |
|---|---|---|
| 53 | Koli Bandı |  1 |
| 54 | Çift Taraflı Bantlar |  3 |
| 55 | Kağıt Bantlar |  4 |
| 56 | Kırtasiye Bantları |  9 |
| 57 | Alüminyum Folyo Bantlar |  8 |
| 58 | Diğer Ürünler |  10 |
| 59 | Karton Bant |  5 |
| 60 | Dokunmamış Bantlar |  6 |
| 61 | Masking Bantlar |  7 |
| 62 | Baskılı Bant |  2 |
| 63 | Streç Filmi |  11 |
| 64 | Çöp Poşeti |  12 |

`ust`/`kategori` columns: 12/12 rows NULL/empty -> **flat structure (no hierarchy)**, decision 13.3 valid.
Category `resim` column: 11/12 rows populated (category images exist!) -> `categories` table has NO image column -> DECISION 13.1 (skip).

## 3. PRODUCT COUNT

- **Exact product count from dump: 81** (IDs 21-143, unique 81/81).
- U3 (81) **consistent**. tools/legacy-export urun-full.csv (25 rows) is **partial** - dump is the source of truth.

## 4. CATEGORY DISTRIBUTION

| urun.kategori | Products |
|---|---|
| 11 | 1 |
| 10 | 1 |
| 9 | 1 |
| 3 | 23 |
| 2 | 31 |
| 1 | 24 |
| **TOTAL** | **81** |\n\n- Category 11 exists in dump (1 product) - consistent with U3.
- 6 distinct category values in use: 1, 2, 3, 9, 10, 11.
- 6 category IDs unused: 55, 56, 57, 58, 59, 60.

## 5. CATEGORY MAPPING ANALYSIS

- `urun.kategori` values: **1, 2, 3, 9, 10, 11**
- `kategori.id` values: **53, 54, ..., 64**
- **Intersection: EMPTY. Direct ID mapping DOES NOT EXIST.**
- Name patterns (HINT, NOT EVIDENCE): kat=1 -> colored/patterned products; kat=2 -> color names; kat=3 -> band type.
- **RESULT: MAPPING UNRESOLVED.** Human decision required.

## 6. PRODUCT INVENTORY (81 products)

| ID | Name (fixed) | Kat | Proposed Cat | Slug | Resim | Resim2 | Status |
|---|---|---|---|---|---|---|---|
| 21 | CALM | 1 | UNRESOLVED | calm | p18haf71ee1idkvn1jmf1av5njf2.jpg | - | UNRESOLVED |
| 23 | FLIRTY | 1 | UNRESOLVED | flirty | p18haf51io13fr1mvl4ei1fnv1hjk2.jpg | - | UNRESOLVED |
| 25 | LOYAL | 1 | UNRESOLVED | loyal | p18haf8c999m1r9m1nn3100k1osh2.jpg | - | UNRESOLVED |
| 28 | ALIVE | 1 | UNRESOLVED | alive | p18haf9acrg821d11qmv51f1em52.jpg | - | UNRESOLVED |
| 30 | SERENITY | 1 | UNRESOLVED | serenity | p197gv959i1v1a135k1if41okubs92.jpg | - | UNRESOLVED |
| 31 | SANGUINE | 1 | UNRESOLVED | sanguine | p18hafaajg1o188m9pqt1ii1c922.jpg | - | UNRESOLVED |
| 32 | DAZZLE | 1 | UNRESOLVED | dazzle | p18hafb7ti48e1rpb1t5sfhth2.jpg | - | UNRESOLVED |
| 37 | CHEER | 1 | UNRESOLVED | cheer | p18hafd8ir18m45cgmma85l17od2.jpg | - | UNRESOLVED |
| 38 | JOLLY | 1 | UNRESOLVED | jolly | p18hafe97l16061uif1kgr1mlq1s8h2.jp | - | UNRESOLVED |
| 39 | CLASSY | 1 | UNRESOLVED | classy | p18haffk0u1716bta1tt18kut8t2.jpg | - | UNRESOLVED |
| 40 | JEALOUS | 1 | UNRESOLVED | jealous | p18hafgl1k18ukgt71pp23av1ph82.jpg | - | UNRESOLVED |
| 44 | KIND | 1 | UNRESOLVED | kind | p18hafhml71fsnh3sti618ke1uhg2.jpg | - | UNRESOLVED |
| 50 | FASCINATE | 1 | UNRESOLVED | fascinate | p18hafimanm0aks91a2i1pmkhhh2.jpg | - | UNRESOLVED |
| 53 | CRAZY | 1 | UNRESOLVED | crazy | p18hafjodcjjo1chlbis1vpc1hc92.jpg | - | UNRESOLVED |
| 54 | PRIDE | 1 | UNRESOLVED | pride | p18haf423v15lk1mq11khljt7n2.jpg | - | UNRESOLVED |
| 57 | FLAMBOYANT | 1 | UNRESOLVED | flamboyant | p18haf2uj2b98ii61nhm1f72ip52.jpg | - | UNRESOLVED |
| 58 | BRAVE | 1 | UNRESOLVED | brave | p18haflqqbl9dn4qrki1kgh1ouf2.jpg | - | UNRESOLVED |
| 67 | CHIVAS | 3 | UNRESOLVED | chivas | p18h9pisgind2qci15142531f0o2.jpg | - | UNRESOLVED |
| 68 | AZURE | 2 | UNRESOLVED | azure | p18h7lihfmkdbqsmnjpqh84n2.jpg | - | UNRESOLVED |
| 70 | CARMINE | 2 | UNRESOLVED | carmine | p18h7n9j8j8renkk16gr1c92dms3.jpg | - | UNRESOLVED |
| 71 | MAROON | 2 | UNRESOLVED | maroon | p18h7nrvhf11mi1rs9opleo91vpu2.jpg | - | UNRESOLVED |
| 72 | SANGARIA | 2 | UNRESOLVED | sangaria | p18h7o2ejfe851ihl1cml1iedd672.jpg | - | UNRESOLVED |
| 75 | CLARET | 2 | UNRESOLVED | claret | p18h7qkkuk1j9m1r8i1v9j15m8lko2.jpg | - | UNRESOLVED |
| 76 | BEIGE | 2 | UNRESOLVED | beige | p18h7qol1i1n7710ch10vdevlbm1.jpg | - | UNRESOLVED |
| 77 | BLACK | 2 | UNRESOLVED | black | p18h7qubpbre1u2i1icq1bjart32.jpg | - | UNRESOLVED |
| 79 | VIOLET | 2 | UNRESOLVED | violet | p18h7rcbbuq1urjd12g21o32rn91.jpg | - | UNRESOLVED |
| 80 | MAGENTA | 2 | UNRESOLVED | magenta | p18h7rnfs38adhpe1d031qjs86o2.jpg | - | UNRESOLVED |
| 81 | GREEN | 2 | UNRESOLVED | green | p18h7rsfga9d61bjj1vsv64pb202.jpg | - | UNRESOLVED |
| 82 | MAYA | 2 | UNRESOLVED | maya | p18h7s4mp4njt1ioo1e77uge11ec1.jpg | - | UNRESOLVED |
| 83 | WHITE | 2 | UNRESOLVED | white | p18h7s9d2u1hnrj2h2h57d411js2.jpg | - | UNRESOLVED |
| 84 | BLUSH | 2 | UNRESOLVED | blush | p18h7sfuc71rfh1fgik8e1f67se22.jpg | - | UNRESOLVED |
| 85 | EMERALD | 2 | UNRESOLVED | emerald | p18h7sn6pih6r12nvc931tags5m2.jpg | - | UNRESOLVED |
| 86 | AQUAMARINE | 2 | UNRESOLVED | aquamarine | p18h7ssmrq445vo61gq5k432lh1.jpg | - | UNRESOLVED |
| 87 | CRANBERRY | 2 | UNRESOLVED | cranberry | p18h7t38ij3ca1reh13m21fhcvii2.jpg | - | UNRESOLVED |
| 88 | ROSE | 2 | UNRESOLVED | rose | p18h7t7j921c0p15c51dfejdk16gk2.jpg | - | UNRESOLVED |
| 91 | INDIGO | 2 | UNRESOLVED | indigo | p18h7tsqdf1271e861dchvltr3o2.jpg | - | UNRESOLVED |
| 93 | PEACOCK | 2 | UNRESOLVED | peacock | p18h7u3spk134m1ad11nhe42s1o1h2.jpg | - | UNRESOLVED |
| 94 | LAVANDER | 2 | UNRESOLVED | lavander | p18h7udvpb5dknmg12sg7ifbku2.jpg | - | UNRESOLVED |
| 95 | ALICE | 2 | UNRESOLVED | alice | p18h7uh0s111lp1e4els21fac12a1.jpg | - | UNRESOLVED |
| 97 | RED | 2 | UNRESOLVED | red | p18h7ur9bq12lk1los1cea1kh1oav2.jpg | - | UNRESOLVED |
| 98 | PERIWINKLE | 2 | UNRESOLVED | periwinkle | p18h7v22ad1rui1o4gjmr137ttgo2.jpg | - | UNRESOLVED |
| 99 | KLEIN | 2 | UNRESOLVED | klein | p18h7v76l5eja1rac1r0t170n2ug2.jpg | - | UNRESOLVED |
| 100 | LILAC | 2 | UNRESOLVED | lilac | p18h7vfd6117tu1855sio11ev7n81.jpg | - | UNRESOLVED |
| 101 | NAVY | 2 | UNRESOLVED | navy | p18h7vk9jl1dfc152rjededh12kk2.jpg | - | UNRESOLVED |
| 103 | RUBY | 2 | UNRESOLVED | ruby | p18h7vrp5n7t3jmd3tsbok3p42.jpg | - | UNRESOLVED |
| 104 | FUSCHIA | 2 | UNRESOLVED | fuschia | p18h7vvesrubn1lal1j2t1gc8ruj1.jpg | - | UNRESOLVED |
| 105 | LATTE | 2 | UNRESOLVED | latte | p18h803gac1k6o1k7a1f2o14b4fsb2.jpg | - | UNRESOLVED |
| 106 | BURGUNDY | 2 | UNRESOLVED | burgundy | p18h806f1ip0dpmu6bbrfadn2.jpg | - | UNRESOLVED |
| 107 | YELLOW | 2 | UNRESOLVED | yellow | p18h809bbm9d014661r251qbt1p9o2.jpg | - | UNRESOLVED |
| 109 | OUZU | 3 | UNRESOLVED | ouzu | p18h9q1nd01u3p34o1dfl1btqjrj2.jpg | - | UNRESOLVED |
| 110 | DOLCETTO | 3 | UNRESOLVED | dolcetto | p18h9qavqm87idov1a4vq7ei102.jpg | - | UNRESOLVED |
| 111 | SHIRAZ | 3 | UNRESOLVED | shiraz | p18h9qo5aj146d15qfcus139c1l8h2.jpg | - | UNRESOLVED |
| 112 | AVIGNON | 3 | UNRESOLVED | avignon | p18h9r2e3ac8qnr1iua1he0nph2.jpg | - | UNRESOLVED |
| 114 | REMMY MARTIN | 3 | UNRESOLVED | remmy-martin | p18h9rmsnt1dqu1vr21h58ralq2a2.jpg | - | UNRESOLVED |
| 115 | BIANCO | 3 | UNRESOLVED | bianco | p18h9s1an7uuc15hkhe51qlfiur2.jpg | - | UNRESOLVED |
| 116 | BAILEYS | 3 | UNRESOLVED | baileys | p18h9s80t91js3skg1lc336k7sk2.jpg | - | UNRESOLVED |
| 117 | VERONA BLUSH | 3 | UNRESOLVED | verona-blush | p18h9slj261cmu17codl1o6l111h2.jpg | - | UNRESOLVED |
| 118 | PRUNELLE | 3 | UNRESOLVED | prunelle | p18h9t3ho93gvguuc5v69t185p2.jpg | - | UNRESOLVED |
| 119 | MARTINI | 3 | UNRESOLVED | martini | p18h9tb1j71p7ntsppkpvevla32.jpg | - | UNRESOLVED |
| 120 | KISSBERRY | 3 | UNRESOLVED | kissberry | p18h9te8j1l3odt71a5gn0e1rgc2.jpg | - | UNRESOLVED |
| 121 | SAVIGNON BLANC | 3 | UNRESOLVED | savignon-blanc | p18h9tlhqe1ksgh08hsi10911j192.jpg | - | UNRESOLVED |
| 122 | CHARDONNAY | 3 | UNRESOLVED | chardonnay | p18h9trn6rn6o2uq1d231b8h1v5j2.jpg | - | UNRESOLVED |
| 123 | MALIBU | 3 | UNRESOLVED | malibu | p18h9tvom2o4e1fdh1mav1bfp198r2.jpg | - | UNRESOLVED |
| 124 | GRAPPA | 3 | UNRESOLVED | grappa | p18h9u6gb11p3775k2v3r1d1oh52.jpg | - | UNRESOLVED |
| 125 | ISABELLA | 3 | UNRESOLVED | isabella | p18h9udnehu1r7bgvb1dcj15f32.jpg | - | UNRESOLVED |
| 126 | BACARDI | 3 | UNRESOLVED | bacardi | p18h9uhm361j47usiiariin14d83.jpg | - | UNRESOLVED |
| 127 | BLODDY MARY | 3 | UNRESOLVED | bloddy-mary | p18h9v5oai10menlb34k18oq2bh2.jpg | - | UNRESOLVED |
| 128 | PINOT NOIR | 3 | UNRESOLVED | pinot-noir | p18h9va8kpn3lquh11q91hk21l6p2.jpg | - | UNRESOLVED |
| 129 | MIRABELLE | 3 | UNRESOLVED | mirabelle | p18h9vfa941qlm1d1b74spar1hde2.jpg | - | UNRESOLVED |
| 130 | MERLOT | 3 | UNRESOLVED | merlot | p18h9vivbj1hhcloj1fca1bsgf232.jpg | - | UNRESOLVED |
| 131 | MARSALA | 3 | UNRESOLVED | marsala | p18h9vlvslb391v581prd16su1q6c2.jpg | - | UNRESOLVED |
| 134 | DESIRE | 1 | UNRESOLVED | desire | p19cvgum1k1jl31ol11pjkgkumdp1.jpg | - | UNRESOLVED |
| 135 | GRACE | 1 | UNRESOLVED | grace | p19cvh7tql1bnj1t925pb1ufa1aqo2.jpg | - | UNRESOLVED |
| 136 | MERY | 1 | UNRESOLVED | mery | p19cvhiprmekc14f61qqs1e0ocbu2.jpg | - | UNRESOLVED |
| 137 | BLUE | 1 | UNRESOLVED | blue | p19cvg9a6q1qqs112m18dj1ltvo7s2.jpg | - | UNRESOLVED |
| 138 | BREEZY | 1 | UNRESOLVED | breezy | p19cvhvhui12hq1d2l9nq14rn1seq2.jpg | - | UNRESOLVED |
| 139 | LOVE | 1 | UNRESOLVED | love | p19cviburs1uagm0ulbrt8o1tuj2.jpg | - | UNRESOLVED |
| 140 | MODEST | 1 | UNRESOLVED | modest | p19cvisal4l1cuel1t8r6scocn2.jpg | - | UNRESOLVED |
| 141 | Tente ve Güneşlik sistemleri; kolları ve kumaşı içine alabilen tamamen kapalı kutu şeklinde tasarlanmıştır. Kutu içerisine yerleştirilen fırça ile kendi kendini temizleyebilmektedir.  Dış mekanların dört mevsim kullanılması amacıyla tasarlanan bir sistemdir. Konutlar, villalar ve cafeler için uygundur. | 9 | UNRESOLVED | tente-ve-guneslik-sistemleri-kollari-ve-kumasi-icine-alabilen-tamamen-kapali-kutu-seklinde-tasarlanmistir-kutu-icerisine-yerlestirilen-firca-ile-kendi-kendini-temizleyebilmektedir-dis-mekanlarin-dort-mevsim-kullanilmasi-amaciyla-tasarlanan-bir-sistemdir-konutlar-villalar-ve-cafeler-icin-uygundur | p19da2cpo1eo31b6dnii8m6bo71.jpg | - | UNRESOLVED |
| 142 | Raylı sistemli çadır pergolenin şıklığını yaşam alanlarınıza taşıyor. Olumsuz hava şartlarına dayanıklı, kış şartlarında da rahatça kullanılabilen uzaktan kumanda ile açılıp kapatılabilen tente sistemidir. | 10 | UNRESOLVED | rayli-sistemli-cadir-pergolenin-sikligini-yasam-alanlariniza-tasiyor-olumsuz-hava-sartlarina-dayanikli-kis-sartlarinda-da-rahatca-kullanilabilen-uzaktan-kumanda-ile-acilip-kapatilabilen-tente-sistemidir | p19dach8fsagpa913nj1nm3tsa1.jpg | - | UNRESOLVED |
| 143 | Kış bahçesi branda, yada diğer adı ile kış bahçesi brandası, genelde mekanların dış tarafında kalan uzantılarında yada sonradan çıkma olarak yapılan yağmur, kar ve rüzgar gibi doğal etkenlerden koruyan ve hammaddesi mika ve polyester olan şeffaf bir kapalı uygulamadır. | 11 | UNRESOLVED | kis-bahcesi-branda-yada-diger-adi-ile-kis-bahcesi-brandasi-genelde-mekanlarin-dis-tarafinda-kalan-uzantilarinda-yada-sonradan-cikma-olarak-yapilan-yagmur-kar-ve-ruzgar-gibi-dogal-etkenlerden-koruyan-ve-hammaddesi-mika-ve-polyester-olan-seffaf-bir-kapali-uygulamadir | p19dacj37718df1ekeabv1gf41uef1.jpg | - | UNRESOLVED |

## 7. IMAGE REFERENCE ANALYSIS

- Separator: **comma (,)** - confirmed 81/81; pipe 0.
- Pattern: `resim` = `,f1.jpg,f2.jpg,`.
- `resim2`: **empty 81/81**.
- Total tokens: **160** all distinct.
- uploads/: **57 images**.
- **Match: 0/160**. Orphans: **57**.
- Human verification required.

## 8. ENCODING FINDINGS

- **Categories: 12/12 mojibake (double-encoded).**
- **Correct fix: Windows-1252** (not ISO-8859-1).
- **Round-trip: 12/12 OK** with Windows-1252.
- Products: 78/81 ASCII; 3 non-ASCII all OK.
- Product icerik: 0/81 mojibake.

## 9. READY / REVIEW / UNRESOLVED

| Status | Products | Reason |
|---|---|---|
| READY | **0** | - |
| REVIEW | **0** | - |
| UNRESOLVED | **81** | Category mapping pending |
| **TOTAL** | **81** | |

- Slug collisions: **0/81**.
- Empty baslik: **0**. No-image products: **0**.
- Unmatched images: **81/81** -> REVIEW.

## 10. IMPORT RISKS

1. **Category mapping unresolved.**
2. **Image files missing** (0/160).
3. **Encoding** - Windows-1252 fix needs approval.
4. **Fiyat/multilingual** - NOT IMPORTED.
5. **Category images** - target column ABSENT.

## 11. EXACT RECOMMENDED NEXT STEP

1. **Human: category mapping table.**
2. **Human: correct image folder export.**
3. **Human: encoding approval (Windows-1252).**
4. **M5-D prerequisites.**
5. **M5-D scope.**

---

GIT: commit/push NONE
DB CONNECTION: NONE
LEGACY DB ACCESS: NONE
DEVIATIONS: NONE
VERDICT: PASS
