(function ($, window, document, undefined) {
    'use strict';

    String.prototype.slugifyTitle = function() {
        return this.toString()               // Convert to string
            .normalize('NFD')               // Change diacritics
            .replace(/[\u0300-\u036f]/g,'') // Remove illegal characters
            .replace(/\s+/g,'-')            // Change whitespace to dashes
            .toLowerCase()                  // Change to lowercase
            .replace(/&/g,'-and-')          // Replace ampersand
            .replace(/[^a-z0-9\-]/g,'')     // Remove anything that is not a letter, number or dash
            .replace(/-+/g,'-')             // Remove duplicate dashes
            .replace(/^-*/,'')              // Remove starting dashes
            .replace(/-*$/,'');             // Remove trailing dashes
     }

    var sr_table = {
        $table: null,
        getSportTypes: function (publicSpaces) {
            let types = '';
            publicSpaces.forEach(function (public_space) {
                public_space.sportTypes.forEach(function (sportType) {
                    types += sportType.name + ', ';
                });
            });
            return types.length > 0 ? types.slice(0, -2) : '-';
        },
        $tables: {
            'sportsbases': {
                url: sr_table_vars.REST_URL + 'sport-register/v1/sportbases',
                columns : [
                    {
                        title: 'Sporto bazės pavadinimas',
                        data: 'name',
                        name: 'name',
                    },
                    {
                        title: 'Rūšis',
                        data: null,
                        name: 'type.name',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return row.type.name ?? '-';
                        }
                    },
                    {
                        title: 'Savivaldybė',
                        data: 'address.municipality',
                        name: 'address.municipality',
                        orderable: false,
                        searchable: false
                    },
                    {
                        title: 'Sporto šakos',
                        data: null,
                        name: 'sportTypes',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return sr_table.getSportTypes(row.publicSpaces);
                        }
                    },
                    {
                        title: 'Erdvių skaičius',
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return Object.keys(row.publicSpaces).length;
                        }
                    },
                    {
                        title: 'Organizacija',
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return 'Kas įvedė informaciją';
                        }
                    },
                    {
                        title: 'Veiksmas',
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            console.log(row);
                            return '<a class="read-more" href="' + sr_table_vars.SPORT_BASE_URL + row.id + (row.name != null ? '/'+row.name.slugifyTitle(): '')+'">' + sr_table_vars.I18N.READ_MORE + '</a>';
                        }
                    }
                ],
                columnDefs: [
                    // {
                    //     targets: 1,
                    //     render: function (data, type, row) {
                    //         return '<a href="' + row.webPage + '">' + data + '</a>';
                    //     }
                    // }
                ]
            },
            'organizations': {
                url: sr_table_vars.REST_URL + 'sport-register/v1/organizations',
                columns : [
                    {
                        title: 'Sporto organizacijos pavadinimas',
                        name: 'name',
                        data: null,
                        render: function (data, type, row) {
                            return row.name ?? '-';
                        }
                    },
                    {
                        title: 'Tipas',
                        data: null,
                        name: 'type',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return row.type !=null ? row.type.name : '-';
                        }
                    },
                    {
                        title: 'Adresas',
                        data: null,
                        name: 'address',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return 'API nėra';
                        }
                    },
                    {
                        title: 'Veiksmas',
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            if (row.name == null){
                                return '-';
                            }else{
                                return '<a class="read-more" href="' + sr_table_vars.SPORT_BASE_URL + row.id + (row.name != null ? '/'+row.name.slugifyTitle(): '')+'">' + sr_table_vars.I18N.READ_MORE + '</a>';
                            }
                        }
                    }
                ],
                columnDefs: [
                    // {
                    //     targets: 1,
                    //     render: function (data, type, row) {
                    //         return '<a href="' + row.webPage + '">' + data + '</a>';
                    //     }
                    // }
                ]
            }
        },
        sanitize_title: function (title) {

            return title.toLowerCase().replace(/ /g, '-');
        },
        createDataTable: function (config) {
            this.$table.DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                searchDelay: 350,
                ajax: {
                    url: config.url,
                    dataSrc: 'data',
                    data: function (d) {
                        d.page = Math.ceil(d.start / d.length) + 1;
                        d.pageSize = d.length;
                        d.sort = d.order[0].dir === 'asc' ? d.columns[d.order[0].column].data : '-' + d.columns[d.order[0].column].data;
                        d.search = d.search.value;
                    },
                    error: function (xhr, error, code) {
                        console.error("Ajax request failed:", error, code);
                    }
                },
                language: {
                    lengthMenu: 'Rodyti po _MENU_ įrašų',
                    zeroRecords: 'Atsiprašome, nieko neradome',
                    info: 'Rodoma: _PAGE_ iš _PAGES_',
                    infoEmpty: 'Nėra jokių įrašų',
                    infoFiltered: '(išfiltruota iš _MAX_ viso įrašų)',
                    search: 'Ieškoti:',
                    paginate: {
                        previous: 'Ankstenis',
                        next: 'Kitas'
                    }
                },
                columns: config.columns,
                columnDefs: config.columnDefs,
                autoWidth: true,
                pageLength: 20,
                bDestroy: true,
                dom: '<"top"<"biip_table_header">Bf>rt<"bottom"ip>',
                responsive: true,
            });
        },
        init: function () {
            this.$table = $('#'+sr_table_vars.TABLE_ID);
            this.createDataTable(this.$tables[sr_table_vars.TABLE_ID]);
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        sr_table.init();
    });  

}(jQuery, window, document));