<html>
    <head>
        <style>
            table {
                border-collapse: collapse;
                width: 60%;
                margin: 20px auto;
                font-family: Arial, sans-serif;
            }

            th {
                background-color: #1e90b6; 
                color: white;
                padding: 10px;
                text-align: center;
            }

            td {
                padding: 10px;
                text-align: center;
                border: 1px solid #ddd;
            }

            tr:nth-child(even) {
                background-color: #f2f9fc; 
            }

            tr:nth-child(odd) {
                background-color: white; 
            }
        </style>
    </head>
    <body>
    
    <table>
        <tr>
            <th>Disciplina</th> 
            <th>Faltas</th>  
            <th>Média</th> 
        </tr>
        <?php
            $tabela = array (array("Matemática", "5", "8.5"),
                            array("Português", "2", "9"),
                            array("Geografia", "10", "6"),
                            array("Educação Física", "2", "8"));
                    
            foreach ($tabela as $Linha_tabela) {
                $Disciplina = $Linha_tabela[0];
                $Faltas = $Linha_tabela[1];
                $Nota = $Linha_tabela[2];
                
                echo "<tr>
                        <td>$Disciplina</td>
                        <td>$Faltas</td>
                        <td>$Nota</td>
                    </tr>";
            }
        ?>
    </table>
    </body>
</html>