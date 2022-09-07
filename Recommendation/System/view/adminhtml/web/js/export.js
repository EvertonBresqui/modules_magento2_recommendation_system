//Carrega o Jquery
require(['jquery', 'jquery/ui'], function($){
     //Carrega objeto
    let Export = {
        tables: [],
        qty_per_cicle: 0,
        url: '',
        links: [],

        init: function(){
            Export.tables = JSON.parse($("#tables").val());
            Export.qty_per_cicle = $("#qty_per_cicle").val();
            Export.url = $("#url").val();
        },
        getNameFile: function(url){
            let urlArray = url.split("/");
            return urlArray[urlArray.length - 1];
        },
        //Faz a exportação
        runExport: function(){
            //Percorre todas as tables para fazer a exportação
            for(let table in Export.tables){
                Export.export(table, 0);
            }
            $('#message').html("&nbsp;Exportação finalizada!");
            if(Export.links.length > 0){
                let linksHtml = "<h3>Arquivos de exportação gerados:<h3>";
                for(let i = 0; i < Export.links.length; i += 1){
                    linksHtml += '<p><a href="'+Export.links[i]+'" target="_blank">'+ Export.getNameFile(Export.links[i]) +'</a></p>';
                }
                $('#links').html(linksHtml);
            }
        },
        export:function(table, qtyExported){
            let data = {
                table: table,
                data: JSON.stringify(Export.tables[table]),
                form_key: $('[name="form_key"]').val()
            };
            $.ajax({
                url: Export.url,
                type: "post",
                data: data,
                async: false,
                dataType: "json",
                success: function (response) {
                    if(response.qty_exported && response.registry_position_exported){
                        //Seta a quantidade exportada no objeto
                        Export.tables[table].qty_exported = response.qty_exported;
                        Export.tables[table].registry_position_exported = response.registry_position_exported;
                        //Exibe a quantidade exportada
                        $('#qty_exported_table_' + table).html(Export.tables[table].qty_exported);
                        //Salva os links dos arquivos de exportação
                        if(response.link_file && Export.links.indexOf(response.link_file,0) === -1)
                            Export.links.push(response.link_file);
                        //Caso ainda não gerou a exportação de todos registros da tabela
                        if(Export.tables[table].qty_exported < Export.tables[table].qty_total &&
                            Export.tables[table].qty_exported > qtyExported){
                            Export.export(table, qtyExported);
                        }
                    }
                    else{
                        alert('Ocorreu um erro ao gerar exportação!');
                    }
                },
                error: function (error) {
                    console.log(error);
                    alert('Ocorreu um erro ao gerar exportação!');
                }
            }); 
        }
    }

    //Espera o carregamento da página
    $(document).ready(function(){
        console.log('run view index recommendation_system');
        Export.init();
        //Inicia a exportação com a permissão do usuário
        $("#btn_export").click( function() {
            Export.runExport();
        });
    });
   
});



