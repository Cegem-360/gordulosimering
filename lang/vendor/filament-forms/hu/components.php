<?php

declare(strict_types=1);

return [
    'builder' => [
        'actions' => [
            'clone' => [
                'label' => 'Duplikálás',
            ],
            'add' => [
                'label' => 'Új :label',
                'modal' => [
                    'heading' => 'Új :label',
                    'actions' => [
                        'add' => [
                            'label' => 'Új',
                        ],
                    ],
                ],
            ],
            'add_between' => [
                'label' => 'Beillesztés blokkok közé',
                'modal' => [
                    'heading' => 'Új :label',
                    'actions' => [
                        'add' => [
                            'label' => 'Új',
                        ],
                    ],
                ],
            ],
            'delete' => [
                'label' => 'Törlés',
            ],
            'edit' => [
                'label' => 'Szerkesztés',
                'modal' => [
                    'heading' => 'Blokk szerkesztése',
                    'actions' => [
                        'save' => [
                            'label' => 'Változtatások mentése',
                        ],
                    ],
                ],
            ],
            'reorder' => [
                'label' => 'Mozgatás',
            ],
            'move_down' => [
                'label' => 'Mozgatás lefelé',
            ],
            'move_up' => [
                'label' => 'Mozgatás felfelé',
            ],
            'collapse' => [
                'label' => 'Becsukás',
            ],
            'expand' => [
                'label' => 'Kibontás',
            ],
            'collapse_all' => [
                'label' => 'Összes becsukása',
            ],
            'expand_all' => [
                'label' => 'Összes kibontása',
            ],
        ],
    ],
    'checkbox_list' => [
        'actions' => [
            'deselect_all' => [
                'label' => 'Kijelölés megszüntetése',
            ],
            'select_all' => [
                'label' => 'Összes kijelölése',
            ],
        ],
    ],
    'file_upload' => [
        'editor' => [
            'actions' => [
                'cancel' => [
                    'label' => 'Mégsem',
                ],
                'drag_crop' => [
                    'label' => 'Méretrevágási mód',
                ],
                'drag_move' => [
                    'label' => 'Mozgatási mód',
                ],
                'flip_horizontal' => [
                    'label' => 'Kép vízszintes tükrözése',
                ],
                'flip_vertical' => [
                    'label' => 'Kép függőleges tükrözése',
                ],
                'move_down' => [
                    'label' => 'Lefele mozgatás',
                ],
                'move_left' => [
                    'label' => 'Balra mozgatás',
                ],
                'move_right' => [
                    'label' => 'Jobbra mozgatás',
                ],
                'move_up' => [
                    'label' => 'Felfele mozgatás',
                ],
                'reset' => [
                    'label' => 'Visszaállítás',
                ],
                'rotate_left' => [
                    'label' => 'Kép elforgatása balra',
                ],
                'rotate_right' => [
                    'label' => 'Kép elforgatása jobbra',
                ],
                'set_aspect_ratio' => [
                    'label' => 'Képarány beállítása :ratio értékre',
                ],
                'save' => [
                    'label' => 'Mentés',
                ],
                'zoom_100' => [
                    'label' => 'Kép nagyítása 100%-ra',
                ],
                'zoom_in' => [
                    'label' => 'Nagyítás',
                ],
                'zoom_out' => [
                    'label' => 'Kicsinyítés',
                ],
            ],
            'fields' => [
                'height' => [
                    'label' => 'Magasság',
                    'unit' => 'px',
                ],
                'rotation' => [
                    'label' => 'Elforgatás',
                    'unit' => 'fok',
                ],
                'width' => [
                    'label' => 'Szélesség',
                    'unit' => 'px',
                ],
                'x_position' => [
                    'label' => 'X',
                    'unit' => 'px',
                ],
                'y_position' => [
                    'label' => 'Y',
                    'unit' => 'px',
                ],
            ],
            'aspect_ratios' => [
                'label' => 'Képarányok',
                'no_fixed' => [
                    'label' => 'Egyéni',
                ],
            ],
            'svg' => [
                'messages' => [
                    'confirmation' => 'Az SVG fájlok szerkesztése nem ajánlott, mivel minőségromláshoz vezethet az átméretezés során.\\n Biztosan szeretnéd folytatni?',
                    'disabled' => 'Az SVG fájlok szerkesztése nem engedélyezett, mivel minőségromláshoz vezethet az átméretezés során.',
                ],
            ],
            'label' => 'Képszerkesztő',
        ],
        'actions' => [
            'download' => [
                'label' => 'Letöltés',
            ],
            'open' => [
                'label' => 'Megnyitás új lapon',
            ],
        ],
    ],
    'key_value' => [
        'actions' => [
            'add' => [
                'label' => 'Sor hozzáadása',
            ],
            'delete' => [
                'label' => 'Sor törlése',
            ],
            'reorder' => [
                'label' => 'Sor mozgatása',
            ],
        ],
        'fields' => [
            'key' => [
                'label' => 'Kulcs',
            ],
            'value' => [
                'label' => 'Érték',
            ],
        ],
        'columns' => [
            'actions' => [
                'label' => 'Műveletek',
            ],
            'reorder' => [
                'label' => 'Átrendezés',
            ],
        ],
    ],
    'markdown_editor' => [
        'toolbar_buttons' => [
            'attach_files' => 'Fájlok csatolása',
            'blockquote' => 'Idézet',
            'bold' => 'Félkövér',
            'bullet_list' => 'Felsorolás',
            'code_block' => 'Kódblokk',
            'heading' => 'Címsor',
            'italic' => 'Dőlt',
            'link' => 'Hivatkozás',
            'ordered_list' => 'Számozott lista',
            'redo' => 'Visszaállítás',
            'strike' => 'Áthúzott',
            'table' => 'Táblázat',
            'undo' => 'Visszavonás',
        ],
        'file_attachments_accepted_file_types_message' => 'A feltöltött fájlok típusa csak a következő lehet: :values.',
        'file_attachments_max_size_message' => 'A feltöltött fájlok nem lehetnek nagyobbak :max kilobájtnál.',
    ],
    'radio' => [
        'boolean' => [
            'true' => 'Igen',
            'false' => 'Nem',
        ],
    ],
    'repeater' => [
        'actions' => [
            'add' => [
                'label' => 'Új :label',
            ],
            'add_between' => [
                'label' => 'Beillesztés blokkok közé',
            ],
            'delete' => [
                'label' => 'Törlés',
            ],
            'clone' => [
                'label' => 'Duplikálás',
            ],
            'reorder' => [
                'label' => 'Mozgatás',
            ],
            'move_down' => [
                'label' => 'Mozgatás lefelé',
            ],
            'move_up' => [
                'label' => 'Mozgatás felfelé',
            ],
            'collapse' => [
                'label' => 'Becsukás',
            ],
            'expand' => [
                'label' => 'Kibontás',
            ],
            'collapse_all' => [
                'label' => 'Összes becsukása',
            ],
            'expand_all' => [
                'label' => 'Összes kibontása',
            ],
        ],
        'columns' => [
            'actions' => [
                'label' => 'Műveletek',
            ],
            'reorder' => [
                'label' => 'Átrendezés',
            ],
        ],
    ],
    'rich_editor' => [
        'dialogs' => [
            'link' => [
                'actions' => [
                    'link' => 'Hivatkozás',
                    'unlink' => 'Hivatkozás törlése',
                ],
                'label' => 'URL',
                'placeholder' => 'URL cím',
            ],
        ],
        'toolbar_buttons' => [
            'attach_files' => 'Fájlok csatolása',
            'blockquote' => 'Idézet',
            'bold' => 'Félkövér',
            'bullet_list' => 'Felsorolás',
            'code_block' => 'Kódblokk',
            'h1' => 'Címsor 1',
            'h2' => 'Címsor 2',
            'h3' => 'Címsor 3',
            'italic' => 'Dőlt',
            'link' => 'Hivatkozás',
            'ordered_list' => 'Számozott lista',
            'redo' => 'Visszaállítás',
            'strike' => 'Áthúzott',
            'underline' => 'Alázhúzott',
            'undo' => 'Visszavonás',
        ],
        'actions' => [
            'grid' => [
                'label' => 'Rács',
                'modal' => [
                    'heading' => 'Rács',
                    'form' => [
                        'preset' => [
                            'label' => 'Előbeállítás',
                            'placeholder' => 'Nincs',
                            'options' => [
                                'two' => 'Kettő',
                                'three' => 'Három',
                                'four' => 'Négy',
                                'five' => 'Öt',
                                'two_start_third' => 'Kettő (első harmad)',
                                'two_end_third' => 'Kettő (utolsó harmad)',
                                'two_start_fourth' => 'Kettő (első negyed)',
                                'two_end_fourth' => 'Kettő (utolsó negyed)',
                            ],
                        ],
                        'columns' => [
                            'label' => 'Oszlopok',
                        ],
                        'from_breakpoint' => [
                            'label' => 'Ettől a törésponttól',
                            'options' => [
                                'default' => 'Mind',
                                'sm' => 'Kis',
                                'md' => 'Közepes',
                                'lg' => 'Nagy',
                                'xl' => 'Extra nagy',
                                '2xl' => 'Dupla extra nagy',
                            ],
                        ],
                        'is_asymmetric' => [
                            'label' => 'Két aszimmetrikus oszlop',
                        ],
                        'start_span' => [
                            'label' => 'Kezdő szélesség',
                        ],
                        'end_span' => [
                            'label' => 'Záró szélesség',
                        ],
                    ],
                ],
            ],
            'text_color' => [
                'label' => 'Szövegszín',
                'modal' => [
                    'heading' => 'Szövegszín',
                    'form' => [
                        'color' => [
                            'label' => 'Szín',
                            'options' => [
                                'slate' => 'Palakő',
                                'gray' => 'Szürke',
                                'zinc' => 'Cink',
                                'neutral' => 'Semleges',
                                'stone' => 'Kő',
                                'mauve' => 'Mályva',
                                'olive' => 'Olíva',
                                'mist' => 'Ködszürke',
                                'taupe' => 'Vakondszürke',
                                'red' => 'Vörös',
                                'orange' => 'Narancs',
                                'amber' => 'Borostyán',
                                'yellow' => 'Sárga',
                                'lime' => 'Limezöld',
                                'green' => 'Zöld',
                                'emerald' => 'Smaragd',
                                'teal' => 'Pávakék',
                                'cyan' => 'Cián',
                                'sky' => 'Égszínkék',
                                'blue' => 'Kék',
                                'indigo' => 'Indigó',
                                'violet' => 'Ibolya',
                                'purple' => 'Lila',
                                'fuchsia' => 'Fukszia',
                                'pink' => 'Rózsaszín',
                                'rose' => 'Rózsa',
                            ],
                        ],
                        'custom_color' => [
                            'label' => 'Egyéni szín',
                        ],
                    ],
                ],
            ],
        ],
        'file_attachments_accepted_file_types_message' => 'A feltöltött fájlok típusa csak a következő lehet: :values.',
        'file_attachments_max_size_message' => 'A feltöltött fájlok nem lehetnek nagyobbak :max kilobájtnál.',
        'mentions' => [
            'no_options_message' => 'Nincs elérhető lehetőség.',
            'no_search_results_message' => 'Egyetlen találat sem illeszkedik a keresésre.',
            'search_prompt' => 'Kezdj el írni a kereséshez...',
            'searching_message' => 'Keresés...',
        ],
        'toolbar' => [
            'label' => 'Szerkesztő eszköztár',
        ],
        'uploading_file_message' => 'Fájl feltöltése...',
        'tools' => [
            'align_center' => 'Középre igazítás',
            'align_end' => 'Jobbra igazítás',
            'align_justify' => 'Sorkizárás',
            'align_start' => 'Balra igazítás',
            'clear_formatting' => 'Formázás törlése',
            'code' => 'Kód',
            'details' => 'Részletek',
            'h4' => '4. szintű címsor',
            'h5' => '5. szintű címsor',
            'h6' => '6. szintű címsor',
            'grid' => 'Rács',
            'grid_delete' => 'Rács törlése',
            'highlight' => 'Kiemelés',
            'horizontal_rule' => 'Vízszintes vonal',
            'lead' => 'Bevezető szöveg',
            'paragraph' => 'Bekezdés',
            'small' => 'Apró szöveg',
            'table' => 'Táblázat',
            'table_delete' => 'Táblázat törlése',
            'table_add_column_before' => 'Oszlop beszúrása elé',
            'table_add_column_after' => 'Oszlop beszúrása mögé',
            'table_delete_column' => 'Oszlop törlése',
            'table_add_row_before' => 'Sor beszúrása fölé',
            'table_add_row_after' => 'Sor beszúrása alá',
            'table_delete_row' => 'Sor törlése',
            'table_merge_cells' => 'Cellák összevonása',
            'table_split_cell' => 'Cella szétvágása',
            'table_toggle_header_row' => 'Fejlécsor be- és kikapcsolása',
            'table_toggle_header_cell' => 'Fejléccella be- és kikapcsolása',
            'text_color' => 'Szövegszín',
        ],
    ],
    'select' => [
        'actions' => [
            'create_option' => [
                'modal' => [
                    'heading' => 'Új elem hozzáadása',
                    'actions' => [
                        'create' => [
                            'label' => 'Hozzáadás',
                        ],
                        'create_another' => [
                            'label' => 'Mentés és új hozzáadása',
                        ],
                    ],
                ],
            ],
            'edit_option' => [
                'modal' => [
                    'heading' => 'Szerkesztés',
                    'actions' => [
                        'save' => [
                            'label' => 'Mentés',
                        ],
                    ],
                ],
            ],
            'clear' => [
                'label' => 'Kijelölés törlése',
            ],
            'remove_option' => [
                'label' => ':label eltávolítása',
            ],
        ],
        'boolean' => [
            'true' => 'Igen',
            'false' => 'Nem',
        ],
        'loading_message' => 'Kérlek várj...',
        'max_items_message' => 'Csak :count elem választható ki.',
        'no_search_results_message' => 'Nincs találat.',
        'placeholder' => 'Válassz ki egy elemet',
        'searching_message' => 'Keresés...',
        'search_prompt' => 'Kezdj el írni a kereséshez...',
        'no_options_message' => 'Nincs elérhető lehetőség.',
        'search_label' => 'Keresés',
    ],
    'tags_input' => [
        'placeholder' => 'Címke hozzáadása',
        'actions' => [
            'delete' => [
                'label' => 'Törlés',
            ],
        ],
        'tag_added' => 'Hozzáadva: :tag',
        'tag_removed' => 'Eltávolítva: :tag',
    ],
    'text_input' => [
        'actions' => [
            'hide_password' => [
                'label' => 'Jelszó elrejtése',
            ],
            'show_password' => [
                'label' => 'Jelszó megjelenítése',
            ],
            'copy' => [
                'label' => 'Másolás',
                'message' => 'Másolva',
            ],
        ],
    ],
    'toggle_buttons' => [
        'boolean' => [
            'true' => 'Igen',
            'false' => 'Nem',
        ],
    ],
    'wizard' => [
        'actions' => [
            'previous_step' => [
                'label' => 'Előző lépés',
            ],
            'next_step' => [
                'label' => 'Következő lépés',
            ],
        ],
    ],
    'color_picker' => [
        'panel_label' => 'Színválasztó',
    ],
    'date_time_picker' => [
        'month_select' => [
            'label' => 'Hónap',
        ],
        'year_input' => [
            'label' => 'Év',
        ],
        'hour_input' => [
            'label' => 'Óra',
        ],
        'minute_input' => [
            'label' => 'Perc',
        ],
        'second_input' => [
            'label' => 'Másodperc',
        ],
    ],
];
