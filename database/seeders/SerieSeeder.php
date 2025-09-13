<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Serie;
use App\Models\Sponsor;
use App\Models\Video;
use App\Models\User;

class SerieSeeder extends Seeder {
    public function run() {
        $seriesToInsert = [
            [
                'slug' => 'cidadania-terceiro-milenio',
                'title' => 'A Cidadania do Terceiro Milênio',
                'tags_ids' => [2,19,24,124],
                'videos' => [
                    [
                        'id' => 'b58fYpvCsE4',
                        'title' => 'Apresentação por Massimo Di Felice',
                    ],
                    [
                        'id' => 'iyQ7pKNqJfc',
                        'title' => 'Ep. 01 – A Cidadania Biosférica por Antônio Donato Nobre (INPE)',
                    ],
                    [
                        'id' => '0Vg6CvpfZ7U',
                        'title' => 'Ep. 02 – A Cidadania das Galáxias por Armâncio França (USP)',
                    ],
                    [
                        'id' => 'eKlVihKJeXE',
                        'title' => 'Ep. 03 – Ecosofia por Michel Maffesoli (França)',
                    ],
                    [
                        'id' => 'asd7GIoxz_A',
                        'title' => 'Ep. 04 – A Info-cidadania por Massimo Di Felice (USP)',
                    ],
                ],
                'description' => 'As inovações tecnológicas, as mudanças climáticas e a pandemia mudaram para sempre a nossa ideia de sociedade. Não habitamos mais a polis e nosso convívio não acontece mais apenas entre sujeitos políticos e seus conflitos de opiniões. Clima, vírus, algoritmos,  dados,  objetos conectados são os novos atores que passaram a fazer parte do nosso comum. Como será a cidadania do terceiro milênio estendida aos não humanos? A Cidadania do Terceiro Milênio mostra, através diversas abordagem disciplinares, como as partículas subatômicas, as tecnologias para a exploração das galáxias, as biodiversidades e as tecnologias de redes de ultima geração serão as novas arquiteturas do nosso comum a partir das quais construiremos outros tipos de cidadania.',
                'researchers' => ['Massimo Di Felice'],
                'thumbnail' => '/images/series/cidadaniaTerceiroMilenio.jpg',
                'thumbnail-novo' => '/images/webseries/cidadania_terceiro_milenio.png',
                'background-image' => '/images/series/cidadania-terceiro-milenio_robynne-hu-unsplash.jpg',
                'background-img' => '/images/series/cidadania-terceiro-milenio_robynne-hu-unsplash.jpg',
                'background-credit' => 'robynne-hu/unsplash',
            ],
            [
                'slug' => 'codice',
                'title' => 'Codice: a vida é digital',
                'tags_ids' => [3, 24, 51, 74, 81, 124],
                'videos' => [
                    [
                        'id' => 'D_IZVQysitc',
                        'title' => 'Ep. 01 – Entrevista com Audrey Tang',
                    ],
                    [
                        'id' => 'xNCa9w51WBM',
                        'title' => 'Ep. 02 – Entrevista com Jacqueline Poh (GovTech)',
                    ],
                    [
                        'id' => 'RYpjvtXzWTE',
                        'title' => 'Ep. 03 – Entrevista com Hiroshi Ishiguro (Inteligência Artificial)',
                    ],
                    [
                        'id' => '4Bu0VsxzBF4',
                        'title' => 'Ep. 04 – Entrevista com Roger Ver (Blockchain e Criptomoedas)',
                    ],
                    [
                        'id' => 'V8ktpgxd77A',
                        'title' => 'Ep. 05 – Entrevista com Sangeet Choudary (Plataformas Digitais)',
                    ],
                    [
                        'id' => 'W1fReWh6WH0',
                        'title' => 'Ep. 06 – Entrevista com Martin Sorrell',
                    ],
                ],
                'description' => 'A série Codice foi criada pela jornalista italiana Barbara Carfagna e reúne um conjunto de entrevistas a alguns dos mais importantes protagonistas da transformação digital da nossa época como Audrey Tang, Ministro de Assuntos Digitais de Taiwan, Jacqueline Poh, presidente da GovTech de Singapura, Hiroshi Ishiguro, professor da Univsidade de Osaka e pesquisador do campo da Inteligência Artificial, Roger Ver, CEO da Bitcoin.com, Sangeet Paul Choudary, autor do livro “Platform Revolution” e Martin Sorrell, megaempresário da comunicação.',
                'researchers' => ['Barbara Carfagna'],
                'thumbnail' => '/images/series/codice.jpg',
                'thumbnail-novo' => '/images/webseries/codice.png',
                'background-image' => '/images/series/codice_homem-vitruviano.jpg',
                'background-img' => '/images/series/codice_homem-vitruviano.jpg',
                'background-credit' => '',
            ],
            [
                'slug' => 'serie-blockchain-cidadania',
                'title' => 'Blockchain para a Cidadania',
                'tags_ids' => [3],
                'videos' => [
                    [
                        'id' => 'xWB7r4D08Lc',
                        'title' => 'Ep. 01 – Blockchain para a Cidadania [Parte 1]',
                    ],
                    [
                        'id' => 'vfXejCFIgmE',
                        'title' => 'Ep. 02 – Blockchain para a Cidadania [Parte 2]',
                    ],
                ],
                'description' => 'A série mostra o poder disruptivos da “internet do valor” e seu potencial de criação de arquiteturas relacionais e de trocas, distribuídas e transparentes. Além dos bitcoin e as diversas criptomoedas, a série mostra o poder da blockchain de criar e gerir  arquiteturas de relações sem mediações e de maneira segura e colaborativa. Um enorme potencial para a gestão do bem publico que irá alterar o significado e a qualidade das relações entre cidadãos e instituições, entre indivíduos e meio-ambiente.',
                'researchers' => ['Tatiana Revoredo', 'Isabella Moura'],
                'thumbnail' => '/images/series/blockchain.jpg',
                'background-image' => '/images/series/blockchain-cidadania_blockchain.jpg',
                'background-img' => '/images/series/blockchain-cidadania_blockchain.jpg',
                'thumbnail-novo' => '/images/webseries/blockchain_cidadania.png',
                'background-credit' => '',
            ],
            [
                'slug' => 'direito-saude-digital',
                'title' => 'Direito a saúde digital',
                'tags_ids' => [1, 23],
                'videos' => [
                    [
                        'id' => 'iw8feq27fPA',
                        'title' => 'Ep. 01 – A Info-cura [Parte 1] por Silvia Surrenti',
                    ],
                    [
                        'id' => 'cRJQz0jcmAg',
                        'title' => 'Ep. 02 – A Info-cura [Parte 2] por Silvia Surrenti',
                    ],
                    [
                        'id' => 'QMZJKxepynI',
                        'title' => 'Ep. 03 – O caso Babylon por Silvia Surrenti',
                    ],
                ],
                'description' => 'A série Direito a Saúde Digital apresenta o impacto das tecnologias digitais, dos Big Data e das formas automatizadas de inteligências no âmbito da saúde. Seja na gestão dos dados do paciente, seja no processo de construção do diagnóstico, na gestão da terapia e da relação medico-paciente o processo de dataficação está redesenhando o cenário da medicina atribuindo à cura, cada vez mais, um sentido informativo e cognitivo. Abre-se assim uma nova fronteira baseada nas possibilidades de gerir a saúde através dos dados e do acesso a formas de monitoramento e a predições algorítmicas. A digitalização da saúde além de melhorar o atendimento e a cura, por meio de técnicas mais rápidas e exatas, permite a redução radical dos custos e a extensão dos benefícios da prevenção e da terapia a um numero maior de pacientes. ',
                'researchers' => ['Silvia Surrenti'],
                'thumbnail' => '/images/series/direitoSaudeDigital.jpg',
                'thumbnail-novo' => '/images/webseries/direito_saude_digital.png',
                'background-image' => '/images/series/direito-saude-digital_national-cancer-institute-unsplash.jpg',
                'background-img' => '/images/series/direito-saude-digital_national-cancer-institute-unsplash.jpg',
                'background-credit' => 'national-cancer-institute/unsplash',
            ],
            [
                'slug' => 'data-ecologia-clima',
                'title' => 'Green Data, Info-ecologias e Mudanças Climáticas ',
                'tags_ids' => [18, 19, 41, 45],
                'videos' => [
                    [
                        'id' => 'DUFjMrIC1Is',
                        'title' => 'Vídeo 1 - Apresetação do verbete Green Data, Ecologia e Mudanças Climáticas por Rita Nardy',
                    ],
                    [
                        'id' => 'QqwZJDEZ9Ng',
                        'title' => 'Vídeo 2 – Programa Beautiful Minds com James Lovelock falando sobre a Teoria de Gaia',
                    ],
                    [
                        'id' => 'UirW2aBP-PY',
                        'title' => 'Vídeo 3 – Como as árvores conversam entre si por uma rede subterrânea por BBC Brasil',
                    ],
                    [
                        'id' => 'Un2yBgIAxYs',
                        'title' => 'Vídeo 4 – How trees talk to each other por Suzanne Simard',
                    ],
                    [
                        'id' => 'gBGt5OeAQFk',
                        'title' => 'Vídeo 5 – Are plants conscious? por Stefano Mancuso em TEDxGranVíaSalon',
                    ],
                    [
                        'id' => 'JDdvd-XC_sI',
                        'title' => 'Vídeo 6 – Rios Voadores por Antônio Donato Nobre (INPE)',
                    ],
                    [
                        'id' => 'zsiTx5qjn7c',
                        'title' => 'Vídeo 7 – Eyes in the forest: saving wildlife in Colombia using camera traps and AI',
                    ],
                    [
                        'id' => 'DSl-9OA_jNo',
                        'title' => 'Vídeo 8 – Roleta Russa Climática',
                    ],
                    [
                        'id' => '6-x8VMK8fPo',
                        'title' => 'Vídeo 9 – Drones for mapping invasive species in Patagonia',
                    ],
                ],
                'description' => 'As últimas gerações de redes, depois das relações sociais (web 2.0), passaram a conectar as coisas (Internet of Things), os dados (Big Data) e, através de sensores, o território e a biodiversidades, gerando uma rede de redes denominada The Internet of Everything. Mais que mídia, as últimas gerações de redes apresentam-se como habitat e ecossistemas relacionais no interior dos quais entidades diversas (humanos, biodiversidades, clima), enquanto transformadas em dados, passam a interagir de outra maneira. Nasce, assim, uma nova relação entre humanos e meio-ambiente não mais baseada na concepção sujeitocêntrica, mas nas interdependências de relações entre entidades-dados. Superando a dimensão sistêmica da sustentabilidade os green data e as info-ecologias permitem o monitoramento continuado das complexidades relacionais de um território assim como a criação de um novo tipo de contratualidade não mais centrada e limitadas aos humanos.',
                'researchers' => ['Aline Pascoalino', 'Rita Machado de Campos Nardy', 'Julliana Cutolo Torres'],
                'thumbnail' => '/images/series/dataEcologiaMudancaClimatica.jpg',
                'thumbnail-novo' => '/images/webseries/greendata.png',
                'background-image' => '/images/series/data-ecologia-clima_agatha-valenca-unsplash.jpg',
                'background-img' => '/images/series/data-ecologia-clima_agatha-valenca-unsplash.jpg',
                'background-credit' => 'agatha-valenca/unsplash',
            ],
            [
                'slug' => 'dialogos-atopicos',
                'title' => 'Diálogos Atópicos',
                'tags_ids' => [1, 3, 13, 124],
                'videos' => [
                    [
                        'id' => 'Xuu6rX1c-K4',
                        'title' => 'Ep. 01 – O Vírus com prof. João Pessoa Araújo Júnior (Unesp)',
                        'text' => 'O primeiro episódio, “O vírus”, traz uma entrevista com o prof. Dr. João Pessoa Araújo Junior, professor Associado do Departamento de Microbiologia e Imunologia da Unesp de Botucatu (SP) e membro da Sociedade Brasileira de Microbiologia. O diálogo aborda o que são os vírus e as suas formas comunicativas com os seres humanos e com outras entidades.',
                    ],
                    [
                        'id' => 'Tn3TE9c9NCU',
                        'title' => 'Ep. 02 – As funções dos algoritmos na pandemia com prof. Mario Pireddu (UniTus, Itália)',
                        'text' => 'O segundo episódio traz uma entrevista com o Prof. Mario Pireddu, professor da Universidade de Tuscia (Itália), onde coordena o curso de Graduação em Formação Digital. O diálogo aborda o que são os algoritmos, como funcionam e que papel desenvolvem, entre outras tecnologias digitais, em tempos de pandemia.',
                    ],
                    [
                        'id' => 'MnKsujuDWOw',
                        'title' => 'Ep. 03 – Pandemia e digitalização do nosso corpo social com prof. Derrick De Kerckhove (Un. de Toronto, Canadá)',
                        'text' => 'No terceiro episódio, o Prof. Derrick de Kerckhove, da Universidade de Toronto (Canadá), aborda reflexões pertinentes que a pandemia de Covid-19 trouxe para a humanidade, entre elas a concepção de sermos cidadãos globais. Ademais, o pensador aborda a relação do vírus com as mudanças climáticas em curso e os impactos que as tecnologias digitais trazem para o cotidiano e para as dinâmicas políticas tradicionais.',
                    ],
                    [
                        'id' => 'bNXRB8iORE4',
                        'title' => 'Ep. 04 – A tecnologia blockchain na pandemia com Tatiana Revoredo (The Oxford Blockchain Foundation)',
                        'text' => 'No quarto episódio, apresentamos uma entrevista com Tatiana Revoredo, especialista em blockchain pela Universidade de Oxford e pelo MIT (EUA), CSO na The Global Strategy,  membro fundadora da Oxford Blockchain Foundation e representante no Brasil do Observatório Europeu do Direito das Novas Tecnologias. A pesquisadora aborda questões sobre o surgimento e funcionamento da tecnologia blockchain e sua colaboração no combate à pandemia do Covid-19.',
                    ],
                    [
                        'id' => 'Jq7RRdkv-xE',
                        'title' => 'Ep. 05 – Especial EAD com profª. Eliane Schlemmer (Unisinos)',
                        'text' => 'O quinto episódio traz uma entrevista com Eliane Schlemmer, professora do programa de pós graduação em Educação da Unisinos e líder do Grupo de Pesquisa Educação Digital do CNPQ, por meio da identidade digital Violet Ladybird. Discutimos assuntos vinculados ao ensino e à educação à distância, como educação on-line,  híbrida e digital. Além disso, abordamos os aprendizados e desafios que a pandemia de coronavírus trouxe para o campo da Educação.',
                    ],
                    [
                        'id' => '_7Sw1YGSM0g',
                        'title' => 'Ep. 06 – Simpoiese Ameríndia com prof. Thiago Franco (UFAM)',
                        'text' => 'O sexto episódio traz uma entrevista com o Prof. Thiago Franco, professor Adjunto da Universidade Federal do Amazonas (UFAM) e membro do Centro Internacional de Pesquisa ATOPOS (USP). Thiago Franco conversa conosco sobre temas como cosmologia ameríndia, conectividades e o conceito de simpoiese. A entrevista conta também com as participações ameríndias de Josias Sateré e Luiz Krahô.',
                    ],
                    [
                        'id' => 'plCYCtfGetk',
                        'title' => 'Ep. 07 – Fake News e pandemia com profª. Teresa Neves (UFJF)',
                        'text' => 'No sétimo e último episódio, a prof. Teresa Neves encerra a primeira temporada do Diálogos Atópicos abordando a verdade e a mentira na era digital, além da relação do fenômeno das Fake News com a pandemia de coronavírus.',
                    ],
                ],
                'description' => 'A série Diálogos Atópicos aborda, a partir de uma perspectiva interdisciplinar, as diversas consequências originadas pela pandemia de Covid-19. Desde as relações socias aos processos decisórios, as  formas de aprendizagem até a economia, cada setor da sociedade foi profundamente afetado e transformado pela disseminação do vírus. Junto com este, difundiu-se a consciência do surgimento de uma nova época caraterizada pelo advento de um novo tipo de complexidade na qual a dúvida e as qualidades emergentes tornam-se o horizonte natural.',
                'researchers' => ['Marina Magalhães', 'Matheus Soares Cruz', 'Teresa Neves', 'Tatiana Revoredo', 'Thiago Cardoso Franco', 'Eliane Schlemmer'],
                'thumbnail' => '/images/series/dialogos-atopicos.jpg',
                'thumbnail-novo' => '/images/webseries/dialogos_atopicos.png',
                'background-image' => '/images/series/dialogos-atopicos_alina-grubnyak-unsplash.jpg',
                'background-img' => '/images/series/dialogos-atopicos_alina-grubnyak-unsplash.jpg',
                'background-credit' => 'alina-grubnyak/unsplash',
            ],
            [
                'slug' => 'unico',
                'title' => 'Masterclasses Identidade e Cidadania Digital - Unico & Atopos/USP',
                'tags_ids' => [],
                'researchers' => ['Massimo Di Felice'],
                'videos' => [
                    [
                        'id' => 'ZeSqVsT2hmA',
                        'title' => 'Introdução ao curso “Identidade e Cidadania Digital"',
                        'text' =>
                            '<b>Professor Massimo di Felice</b> - Coordenador do Centro Internacional de Pesquisa Atopos e Professor da USP. <br>
                        <b>Pedro Henrique Oliveira (Peagá Oliveira)</b> - Diretor de Comunicação da Unico. ',
                        'description' => 'As últimas gerações de redes conectam pessoas, dispositivos, coisas, dados, elementos da natureza e tudo mais que nos cerca. Esse é um novo tipo de ambiente não mais habitado apenas por humanos. Hoje, cada pessoa física e biológica corresponde a uma outra digital, semelhante e feita de dados.',
                        'authors' => ['Massimo Di Felice', 'Pedro Henrique Oliveira (Peagá Oliveira)'],
                    ],
                    [
                        'id' => 'xPSBviVQ0d8',
                        'title' => 'Aula 1 - Identidades digitais não humanas',
                        'text' => '<b>Massimo di Felice</b> - Coordenador do Centro Internacional de Pesquisa Atopos e Professor da USP<br><br>
As últimas gerações de redes conectam pessoas, dispositivos, coisas, dados, elementos da natureza e tudo mais que nos cerca. Esse é um novo tipo de ambiente não mais habitado apenas por humanos. Hoje, cada pessoa física e biológica corresponde a uma outra digital, semelhante e feita de dados. Sim, você é um infovíduo! Nesta aula, são desvendadas as características e a complexidade dessa nova condição.
',
                        'description' => 'As últimas gerações de redes conectam pessoas, dispositivos, coisas, dados, elementos da natureza e tudo mais que nos cerca. Esse é um novo tipo de ambiente não mais habitado apenas por humanos. Hoje, cada pessoa física e biológica corresponde a uma outra digital, semelhante e feita de dados. Sim, você é um infovíduo! Nesta aula, são desvendadas as características e a complexidade dessa nova condição',
                        'authors' => ['Massimo Di Felice'],
                    ],
                    [
                        'id' => 'nLEF7PrvqLc',
                        'title' => 'Aula 2 - Digital twin: a identidade na época do onlife',
                        'text' => '<b>Derrick de Kerckhove</b> - Autor de The Skin of Culture and Connected Intelligence e professor na Universidade de Toronto - Canadá. Foi Diretor do Programa McLuhan em Cultura e Tecnologia de 1983 a 2008.<br><br>
A evolução da internet superou as barreiras iniciais que separavam o mundo real do virtual. A conexão e os dispositivos móveis, num primeiro momento, e as plataformas digitais e o metaverso, atualmente, permitem a experiência de uma condição "onlife" - uma mescla de corpos, dados, arquiteturas físicas e informativas. O conteúdo traz essa transformação digital e o surgimento do conceito de digital twin ou gêmeo digital.
',
                        'description' => 'A evolução da internet superou as barreiras iniciais que separavam o mundo real do virtual. A conexão e os dispositivos móveis, num primeiro momento, e as plataformas digitais e o metaverso, atualmente, permitem a experiência de uma condição "onlife" - uma mescla de corpos, dados, arquiteturas físicas e informativas. O conteúdo traz essa transformação digital e o surgimento do conceito de digital twin ou gêmeo digital.',
                        'authors' => ['Derrick de Kerckhove'],
                    ],
                    [
                        'id' => 'J1blB3gLFbc',
                        'title' => 'Aula 3 - Privacidade e Open Data',
                        'text' => '<b>Yasodara Cordova</b> – Ativista de privacidade na Internet há mais de 15 anos e pesquisadora-chefe de Privacidade da Unico.<br><br>
O Open Data, ou dados abertos, significa que dados podem ser acessados por pessoas, empresas, instituições, times de pesquisa de forma livre, fácil e ágil. O que parece tentador para a prestação de serviços e até mesmo inovação guarda uma outra face: a ameaça à privacidade. Nesta masterclass, todo esse movimento reforça a necessidade de garantirmos segurança e proteção de dados pessoais, que são fundamentais inclusive para estabelecer relações de confiança e limites na sociedade.
',
                        'description' => 'O Open Data, ou dados abertos, significa que dados podem ser acessados por pessoas, empresas, instituições, times de pesquisa de forma livre, fácil e ágil. O que parece tentador para a prestação de serviços e até mesmo inovação guarda uma outra face: a ameaça à privacidade. Nesta masterclass, todo esse movimento reforça a necessidade de garantirmos segurança e proteção de dados pessoais, que são fundamentais inclusive para estabelecer relações de confiança e limites na sociedade.',
                        'authors' => ['Yasodara Cordova'],
                    ],
                    [
                        'id' => 'wPcGA_PWGig',
                        'title' => 'Aula 4 - As identidades indígenas nas redes digitais',
                        'text' => '<b>Eliete Pereira</b> - Doutora em Ciências da Comunicação pela Escola de Comunicações e Artes da USP e pesquisadora do Centro de Pesquisa ATOPOS (ECA/USP)<br><br>
Nos últimos anos, a presença das culturas indígenas nas redes cresceu de forma relevante. Esta masterclass vai apresentar como a digitalização dos povos ameríndios significou a criação de um novo tipo de protagonismo baseado na defesa de seus territórios e na digitalização de idiomas, mitos e visões de mundo.
',
                        'description' => 'Nos últimos anos, a presença das culturas indígenas nas redes cresceu de forma relevante. Esta masterclass vai apresentar como a digitalização dos povos ameríndios significou a criação de um novo tipo de protagonismo baseado na defesa de seus territórios e na digitalização de idiomas, mitos e visões de mundo.',
                        'authors' => ['Eliete Pereira'],
                    ],
                    [
                        'id' => '9vAX6CrA8m4',
                        'title' => 'Aula 5 – Liderança Indígena: conexão entre povos e diversidade na era digital',
                        'text' => '<b>Sonia Guajajara</b> – Atual ministra do Ministério dos povos Indígenas. Foi a primeira Deputada Federal Indígena em São Paulo, eleita em 2022. No mesmo ano, foi incluída na lista das 100 pessoas mais influentes da revista Time, publicada nos EUA.<br><br>
A internet foi um grande ponto de virada para o movimento indígena. Nos últimos anos, indígenas, principalmente mulheres, têm assumido cada vez mais protagonismo nas redes sociais e na sociedade, em prol da conexão com demais povos e do respeito à diversidade. Este conteúdo vai apresentar exemplos desse movimento por meio da voz de uma das principais lideranças indígenas da atualidade.
',
                        'description' => 'A internet foi um grande ponto de virada para o movimento indígena. Nos últimos anos, indígenas, principalmente mulheres, têm assumido cada vez mais protagonismo nas redes sociais e na sociedade, em prol da conexão com demais povos e do respeito à diversidade. Este conteúdo vai apresentar exemplos desse movimento por meio da voz de uma das principais lideranças indígenas da atualidade.',
                        'authors' => ['Sonia Guajajara'],
                    ],
                ],
                'description' => 'Com a velocidade e complexidade das inovações tecnológicas, pode ser desafiador se manter atualizado nos temas da Era Digital e compreender as implicações atuais e futuras em nossas vidas, na carreira e na própria sociedade. Pensando nisso, a Unico, empresa brasileira especializada em identidade digital, fez uma parceria com o Centro Internacional de Pesquisa Atopos da Escola de Comunicações e Artes da Universidade de São Paulo (ECA/USP). Dessa colaboração inédita, surgiram as Masterclasses Identidade e Cidadania Digital - Unico & Atopos/USP, que são gratuitas e já estão disponíveis online.
<br><br>
O objetivo deste projeto é proporcionar um maior acesso a conceitos e aplicações sobre identidade e cidadania digital para que cada pessoa se torne cada vez mais consciente de sua atuação, suas oportunidades e seus direitos diante das rápidas transformações tecnológicas. A identidade é o ponto de partida para a definição e valorização de cada indivíduo em sua singularidade, bem como de uma comunidade. Já a identidade digital traz elementos adicionais como controle de dados, segurança e privacidade e é o elemento fundamental para o exercício da cidadania digital.
<br><br>
Ao todo, são cinco aulas ministradas por professores e profissionais de mercado que são referências em seus temas. Abertas ao público em geral, incluindo as redes de universidades públicas e privadas do Brasil, esta é mais uma oportunidade de contribuir não apenas na formação universitária, mas na empregabilidade e troca de experiências. A seguir, conheça os conteúdos de cada masterclass.
            ',
                'thumbnail' => '',
                'background-image' => '/images/series/dialogos-atopicos_alina-grubnyak-unsplash.jpg',
                'background-credit' => 'alina-grubnyak/unsplash',
                'background-img' => '/images/masterclasses-background.png',
                'logo-title' => '/images/logos/masterclasses.png',
                'sponsors' => [
                    '/images/logos/atopos.png',
                    '/images/logos/cidig.png',
                    '/images/logos/unico.png',
                ],
            ],
        ];

        foreach ($seriesToInsert as $serieToInsert) {
            $serie = Serie::where('slug', data_get($serieToInsert, 'slug'))->first();

            if(!$serie) {
                $serie = Serie::create([
                    'slug' => data_get($serieToInsert, 'slug'),
                    'title' => data_get($serieToInsert, 'title'),
                    'description' => data_get($serieToInsert, 'description'),
                    'thumbnail' => data_get($serieToInsert, 'thumbnail'),
                    'thumbnail_novo' => data_get($serieToInsert, 'thumbnail-novo'),
                    'background_image' => data_get($serieToInsert, 'background-image'),
                    'background_img' => data_get($serieToInsert, 'background-img'),
                    'background_credit' => data_get($serieToInsert, 'background-credit'),
                ]);
            }

            // Criação do sponsor
            $sponsorsToInsert = data_get($serieToInsert, 'sponsors');

            if ($sponsorsToInsert) {
                foreach ($sponsorsToInsert as $sponsorToInsert) {
                    if (preg_match('/\/([^\/]+)\.png$/', $sponsorToInsert, $matches)) {
                        $nomeSponsor = $matches[1];

                        $sponsor = Sponsor::where('name', $nomeSponsor)->first();

                        if(!$sponsor) {
                            $sponsor = Sponsor::create([
                                'name' => $nomeSponsor,
                                'image' => $sponsorToInsert,
                            ]);

                            if (!$serie->sponsors->contains($sponsor->id)) {
                                $serie->sponsors()->attach($sponsor->id);
                            }
                        }

                    }
                }
            }


            // Criação das tags
            $serie->tags()->sync(data_get($serieToInsert, 'tags_ids'));

            // Criação dos vídeos
            $videosToInsert = data_get($serieToInsert, 'videos');

            foreach ($videosToInsert as $videoToInsert) {
                $video = Video::where('video_id', data_get($videoToInsert, 'id'))->first();

                if(!$video) {
                    $video = Video::create([
                        'video_id' => data_get($videoToInsert, 'id'),
                        'title' => data_get($videoToInsert, 'text'),
                        'description' => data_get($videoToInsert, 'description'),
                    ]);
                }

                $usersToInsert = data_get($videoToInsert, 'authors');

                if($usersToInsert) {
                    foreach ($usersToInsert as $userToInsert) {
                        $user = User::where('name', $userToInsert)->first();

                        if(!$user) {
                            dd("O usuário {$userToInsert} não existe. Faça o cadastro dele!");
                        }

                        $video->users()->attach($user->id);
                    }
                }
            }
        }
    }
}
