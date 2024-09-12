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
                searching: false,
                filters: {},
                url: sr_table_vars.REST_URL + 'sport-register/v1/sportbases',
                columns : [
                    {
                        title: 'Sporto bazės pavadinimas',
                        data: 'name',
                        name: 'name',
                        orderable: true,
                        searchable: true,
                        render: function (data, type, row) {
                            const slug = row.name ? `/${row.name.slugifyTitle()}` : '';
                            return `<a class="read-more" href="${sr_table_vars.SPORT_BASE_URL}${row.id}${slug}">${row.name}</a>`;
                        }
                    },
                    {
                        title: 'Rūšis',
                        name: 'type',
                        data: 'type',
                        searchable: false,
                        render: function (data, type, row) {
                            if(row.type != null && row.type.name != null) {
                                return row.type.name;
                            } else {
                                return '-';
                            }
                        }
                    },
                    {
                        title: 'Savivaldybė',
                        data: 'address.municipality.name',
                        name: 'address.municipality.name',
                        orderable: true,
                        searchable: true,
                        render: function (data, type, row) {
                            return row.address?.municipality?.name ?? '-'; 
                        }
                    },                    
                    {
                        title: 'Sporto šakos',
                        data: 'sportTypes',
                        name: 'sportTypes',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            if (Array.isArray(row.sportTypes) && row.sportTypes.length > 0) {
                                return row.sportTypes.map(sportType => sportType.name).join(', ');
                            } else {
                                return '-'; 
                            }
                        }
                    },                    
                    {
                        title: 'Erdvių skaičius',
                        data: null,
                        name: 'spaces',
                        orderable: true,
                        searchable: false,
                        render: function (data, type, row) {
                            return Object(row.spaces).length || '-';
                        }
                    },
                    {
                        title: 'Organizacija',
                        data: null,
                        name: 'tenant.name',
                        render: function (data, type, row) {
                            return row.tenant?.name ?? '-';
                        },
                        orderable: true,
                        searchable: true
                    }
                ],
                columnDefs: [],
                initFilters: function() {
                    $('#filter_sportbase_name').on('keyup', function() {
                        sr_table.$tables['sportsbases'].applyFilters();
                    });
                    $('.filter-checkbox, #filter_sportbase_accessility').on('change', function() {
                        sr_table.$tables['sportsbases'].applyFilters();
                    });
                    $('#clearFilters').on('click', function() {
                        sr_table.$tables['sportsbases'].clearFilters();
                    });

                    $('.dropdown-toggle').on('click', function() {
                        var dropdown = $(this).closest('.dropdown');
                        dropdown.toggleClass('active');
                    });
                    
                    $('.filter-checkbox').on('change', function() {
                        var dropdown = $(this).closest('.dropdown');
                        sr_table.$tables['sportsbases'].updateSelectedCount(dropdown);
                    });
                    $(window).on('click', function(e) {
                        $('.dropdown').each(function() {
                            if (!$(this).is(e.target) && $(this).has(e.target).length === 0) {
                                $(this).removeClass('active');
                            }
                        });
                    });
                },
                applyFilters: function() {
                    var filter_sportbase_name = $('#filter_sportbase_name').val().toLowerCase();
                    var filter_sportbase_type = $('#filter_sportbase_type_form input:checked').map(function() {
                        return this.value;
                    }).get().join('|');
                    var filter_sportbase_sport = $('#filter_sportbase_sport_form input:checked').map(function() {
                        return this.value;
                    }).get().join('|');
                    var filter_sportbase_municipality = $('#filter_sportbase_municipality_form input:checked').map(function() {
                        return this.value;
                    }).get().join('|');
                    var filter_sportbase_accessility = $('#filter_sportbase_accessility').is(':checked') ? 1 : '';
                    
                    sr_table.$tables['sportsbases'].filters = {
                        name: filter_sportbase_name,
                        type: filter_sportbase_type,
                        sport: filter_sportbase_sport,
                        municipality: filter_sportbase_municipality,
                        accessibility: filter_sportbase_accessility
                    };
                    sr_table.$table.draw();
                },
                updateSelectedCount: function(dropdown) {
                    var selectedCount = dropdown.find('.filter-checkbox:checked').length;
                    dropdown.find('.selected-count').text(selectedCount > 0 ? `+${selectedCount}` : '');
                },
                clearFilters: function() {
                    $('#filter_sportbase_name').val('');
                    $('.filter-checkbox, #filter_sportbase_accessility').prop('checked', false);
                    $('.selected-count').text('');
                    sr_table.$tables['sportsbases'].filters = {};
                    sr_table.$table.search('').columns().search('').draw();
                },
                serverSide: true
            },
            'sportpersons': {
                url: sr_table_vars.REST_URL + 'sport-register/v1/sportpersons',
                searching: false,
                filters: {},
                columns : [
                    {
                        title: 'Sporto šaka',
                        data: 'sportTypeName',
                        name: 'sportTypeName',
                        orderable: true,
                        searchable: true,
                    },
                    {
                        title: 'Treneriai',
                        name: 'coach',
                        data: 'coach',
                        render: function (data, type, row) {
                            return row.coach ?? '-';
                        }
                    },
                    {
                        title: 'Teisėjai',
                        name: 'referee',
                        data: 'referee',
                        render: function (data, type, row) {
                            return row.referee ?? '-';
                        }
                    },
                    {
                        title: 'AMS* instruktoriai',
                        name: 'amsInstructor',
                        data: 'amsInstructor',
                        render: function (data, type, row) {
                            return row.amsInstructor ?? '-';
                        }
                    },
                    {
                        title: 'FA* specialistai',
                        name: 'faSpecialist',
                        data: 'faSpecialist',
                        render: function (data, type, row) {
                            return row.faSpecialist ?? '-';
                        }
                    },
                    {
                        title: 'FA* instruktoriai',
                        name: 'faInstructor',
                        data: 'faInstructor',
                        render: function (data, type, row) {
                            return row.faInstructor ?? '-';
                        }
                    },
                    {
                        title: 'Sportininkai',
                        name: 'athlete',
                        data: 'athlete',
                        render: function (data, type, row) {
                            return row.athlete ?? '-';
                        }
                    }
                ],
                columnDefs: [],
                initFilters: function() {
                    $('.filter-checkbox').on('change', function() {
                        sr_table.$tables['sportpersons'].applyFilters();
                    });
                    $('#clearFilters').on('click', function() {
                        sr_table.$tables['sportpersons'].clearFilters();
                    });

                    $('.dropdown-toggle').on('click', function() {
                        var dropdown = $(this).closest('.dropdown');
                        dropdown.toggleClass('active');
                    });
                    
                    $('.filter-checkbox').on('change', function() {
                        var dropdown = $(this).closest('.dropdown');
                        sr_table.$tables['sportpersons'].updateSelectedCount(dropdown);
                    });
                    $(window).on('click', function(e) {
                        $('.dropdown').each(function() {
                            if (!$(this).is(e.target) && $(this).has(e.target).length === 0) {
                                $(this).removeClass('active');
                            }
                        });
                    });
                },
                applyFilters: function() {
                    var filter_sportpersons_sport = $('#filter_sportpersons_sport_form input:checked').map(function() {
                        return this.value; 
                    }).get().join('|');

                    sr_table.$tables['sportpersons'].filters = {
                        sport: filter_sportpersons_sport
                    };
                    sr_table.$table.draw();
                },             
                updateSelectedCount: function(dropdown) {
                    var selectedCount = dropdown.find('.filter-checkbox:checked').length;
                    dropdown.find('.selected-count').text(selectedCount > 0 ? `+${selectedCount}` : '');
                },
                clearFilters: function() {
                    $('.filter-checkbox').prop('checked', false);
                    $('.selected-count').text('');
                    sr_table.$table.search('').columns().search('').draw();
                },
                serverSide: true
            },
            'organizations': {
                url: sr_table_vars.REST_URL + 'sport-register/v1/organizations',
                searching: false,
                filters: {},
                columns : [
                    {
                        title: 'Sporto organizacijos pavadinimas',
                        name: 'name',
                        data: 'name',
                        render: function (data, type, row) {
                            if (!row.name) {
                                return '-';
                            }
                            const slug = row.name ? `/${row.name.slugifyTitle()}` : '';
                            return `<a class="read-more" href="${sr_table_vars.SPORT_BASE_URL}${row.id}${slug}">${row.name}</a>`;
                        }
                    },
                    {
                        title: 'Tipas',
                        name: 'type',
                        data: 'type',
                        searchable: false,
                        render: function (data, type, row) {
                            if(row.type != null && row.type.name != null) {
                                return row.type.name;
                            } else {
                                return '-';
                            }
                        }
                    },
                    {
                        title: 'Adresas',
                        name: 'address',
                        data: 'address',
                        searchable: false,
                        render: function (data, type, row) {
                            return row.address == ''?'-':row.address;
                        }
                    }
                ],
                columnDefs: [
                ],
                initFilters: function() {
                    $('#filter_organization_name').on('keyup', function() {
                        sr_table.$tables['organizations'].applyFilters();
                    });
                    $('.filter-checkbox, #filter_organization_support, #filter_organization_nvo, #filter_organization_nvs').on('change', function() {
                        sr_table.$tables['organizations'].applyFilters();
                    });
                    $('#clearFilters').on('click', function() {
                        sr_table.$tables['organizations'].clearFilters();
                    });

                    $('.dropdown-toggle').on('click', function() {
                        var dropdown = $(this).closest('.dropdown');
                        dropdown.toggleClass('active');
                    });
                    
                    $('.filter-checkbox').on('change', function() {
                        var dropdown = $(this).closest('.dropdown');
                        sr_table.$tables['organizations'].updateSelectedCount(dropdown);
                    });
                    $(window).on('click', function(e) {
                        $('.dropdown').each(function() {
                            if (!$(this).is(e.target) && $(this).has(e.target).length === 0) {
                                $(this).removeClass('active');
                            }
                        });
                    });
                },
                applyFilters: function() {
                    var filter_organization_name = $('#filter_organization_name').val().toLowerCase();
                    var filter_organization_type = $('#filter_organization_type_form input:checked').map(function() {
                        return this.value;
                    }).get().join('|');
                    var filter_organization_sport = $('#filter_organization_sport_form input:checked').map(function() {
                        return this.value;
                    }).get().join('|');
                    var filter_organization_nvo = $('#filter_organization_nvo').is(':checked') ? 1 : '';
                    var filter_organization_nvs = $('#filter_organization_nvs').is(':checked') ? 1 : '';
                    var filter_organization_support = $('#filter_organization_support').is(':checked') ? 1 : '';
                    
                    sr_table.$tables['organizations'].filters = {
                        name: filter_organization_name,
                        type: filter_organization_type,
                        sport: filter_organization_sport,
                        nvo: filter_organization_nvo,
                        nvs: filter_organization_nvs,
                        support: filter_organization_support
                    };
                    sr_table.$table.draw();
                },
                updateSelectedCount: function(dropdown) {
                    var selectedCount = dropdown.find('.filter-checkbox:checked').length;
                    dropdown.find('.selected-count').text(selectedCount > 0 ? `+${selectedCount}` : '');
                },
                clearFilters: function() {
                    $('#filter_organization_name').val('');
                    $('.filter-checkbox, #filter_organization_support, #filter_organization_nvo, #filter_organization_nvs').prop('checked', false);
                    $('.selected-count').text('');
                    sr_table.$tables['organizations'].filters = {};
                    sr_table.$table.search('').columns().search('').draw();
                },
                serverSide: true
            },
        },
        createDataTable: function (config) {
            
            sr_table.$table = $('#'+sr_table_vars.TABLE_ID).DataTable({
                searching: config.searching,
                lengthChange: false,
                info: false,
                processing: config.processing !== undefined ? config.processing : true,
                serverSide: config.serverSide !== undefined ? config.serverSide : true,
                deferRender: config.deferRender !== undefined ? config.deferRender : true,
                searchDelay: 350,
                ajax: {
                    url: config.url,
                    dataSrc: 'data',
                    data: function (d) {
                        d.page = Math.ceil(d.start / d.length) + 1;
                        d.pageSize = d.length;
                        d.sort = d.order[0].dir === 'asc' ? d.columns[d.order[0].column].data : '-' + d.columns[d.order[0].column].data;

                        config.filters && Object.keys(config.filters).forEach(function (key) {
                            d[key] = config.filters[key];
                        });
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
                pageLength: 10,
                bDestroy: true,
                dom: '<"top"<"biip_table_header">Bf>rt<"bottom"ip>',
                responsive: true,
            });
            config.initFilters();
        },
        init: function () {
            this.createDataTable(this.$tables[sr_table_vars.TABLE_ID]);
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        sr_table.init();
    });  

}(jQuery, window, document));