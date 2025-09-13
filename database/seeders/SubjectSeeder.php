<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;
use App\Models\User;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subjectsToInsert = [
            [
                'name' => 'Blockchain para Cidadania',
                'image' => '/images/subjects/blockchainCidadania.jpg',
                'slug' => 'blockchain-cidadania',
                'subject_id' => 2,
                'videoId' => 'ruHWtX_pZkw',
                'description' => 'Blockchain é um veículo sem precedentes para digitalização da sociedade e transferência de valor,, viabilizando a junção do mundo off-line com o mundo on-line na Web3 – a internet democratica. Considerando que os “17 objetivos sustentáveis da ONU até 2030” para acabar com a pobreza e a fome no mundo, contam com a tecnologia blockchain para integrar indivíduos, empresas e governos, as arquiteturas blockchain impactam diretamente o desenvolvimento e exercício da cidadania digital.',
                'researchers' => ['Tatiana Revoredo', 'Isabella Moura'],
                'background-image' => '/images/subjects/blockchain.jpg',
                'background-credit' => '',
            ],
            [
                'name' => 'Design e Plataformas digitais',
                'image' => '/images/subjects/designPlataformasDigitais.jpg',
                'slug' => 'design-plataformas-digitais',
                'subject_id' => 5,
                'videoId' => null,
                'description' => 'Acompanhando as transformações qualitativas tanto resultantes das sucessivas crises sociais, políticas, econômicas e ecológicas atribuídas ao pensamento dualista moderno, quanto o desenvolvimento e a difusão das tecnologias e plataformas digitais, o entendimento sobre design tem sido ressignificado tanto em extensão como profundidade, estendendo sua área de atuação para além dos objetos e peças gráficas cotidianos, alcançando cidades, paisagens, nações, culturas, genes e a própria natureza. A reflexão e o pensamento sobre o design elaborado por teóricos do campo, antropólogos, filósofos, sociólogos, historiados, cientistas da técnica, entre outros, tem servido como substrato para compreensão do nosso modo de ser no mundo e na busca por entender e reelaborar ontologias alternativas àquela oriunda da modernidade. Neste sentido, o verbete Design e Plataformas Digitais, visa abordar esses debates e reflexões, reunindo material que nos auxilie a pensar o papel do design no desenvolvimento da cidadania do terceiro milênio, na construção dos nossos espaços de participação que constituirão o nosso habitar e, consequentemente, na elaboração de uma nova ideia de social e sociedade.',
                'researchers' => ['Bruno Madureira Ferreira'],
                'background-image' => '/images/subjects/design-plataformas-digitais_victor-garcia-unsplash.jpg',
                'background-credit' => 'victor-garcia/unsplash',
            ],
            [
                'name' => 'Ecologia da Informação: Big Data, Fake News e Mundos Possíveis.',
                'image' => '/images/subjects/ecologiaDaInformacao.jpg',
                'slug' => 'ecologia-informacao',
                'subject_id' => 7,
                'videoId' => 'oKINxlw6mMA',
                'description' => 'O advento da tecnologia digital e sua disseminação em escala planetária abalou os alicerces que sustentaram a percepção sobre o que seja verdade. No ambiente interativo das redes, a produção e a partilha de informações tornam-se incontroláveis, porque não podem mais permanecer restritas às fontes convencionais que detinham o domínio sobre sua emissão e circulação. As redes conectivas e interativas que habitamos romperam a lógica hierárquica emissor-receptor própria da comunicação de massa e instalaram em seu lugar uma permanente instabilidade quanto ao que pode ou não ser considerado verdadeiro ou confiável.',
                'researchers' => ['Teresa Neves'],
                'background-image' => '/images/subjects/ecologia-informacao_markus-spiske-unsplash.jpg',
                'background-credit' => 'markus-spiske/unsplash',
            ],
            [
                'name' => 'Games para Cidadania',
                'image' => '/images/subjects/gamesParaCidadania.jpg',
                'slug' => 'games-cidadania',
                'subject_id' => 9,
                'videoId' => 'iLQfTFIs7DE',
                'description' => 'Os games são produtos midiáticos interativos transdisciplinares com recursos artísticos, linguagem computacional e a construção da lógica de interação e ludicidade. Se trata de um produto complexo de ser e estar em um meio natural, artificial e, essencialmente, informacional, como é a cidadania digital. Assim, este verbete abarca a curadoria e mapeamento para identificar os games que tratam direta ou indiretamente a questão da cidadania. Também propõe investigar os conceitos de cidadania digital nos games e desenvolver um framework de game design para a cidadania digital.',
                'researchers' => ['Leandro Key Higuchi Yanaze', 'Ian Dawsey'],
                'background-image' => '/images/subjects/games-cidadania_minh-pham-unsplash.jpg',
                'background-credit' => 'minh-pham/unsplash',
            ],
            [
                'name' => 'Governança Digital',
                'image' => '/images/subjects/governancaDigital.jpg',
                'slug' => 'governanca-digital',
                'subject_id' => 8,
                'videoId' => 'wRSy08pNOSk',
                'description' => 'O estudo da Governança digital deve ser compreendido como um amplo ecossistema de interações entre cidadãos, redes de informações e Estado. Esse modelo aponta para um debate que envolve o tema do e-gov, mas vai além. Discute-se, também, as novas formas de governança digital levadas à cabo pelas Civc Techs e pelas GovTechs, o ciclo de políticas públicas e sua interação com a tecnologia, os desafios da contratação e inovação no poder público e o ecossistema de inovação surgido na sociedade civil para aprimorar a governança digital.',
                'researchers' => ['Erick Roza', 'Michelle Dias'],
                'background-image' => '/images/subjects/governanca-digital_gustavo-leighton-unsplash.jpg',
                'background-credit' => 'gustavo-leighton/unsplash',
            ],
            [
                'name' => 'Green Data, Ecologia e Mudanças Climáticas Globais',
                'image' => '/images/subjects/greendataEcologiaMudancasAmbientais.jpg',
                'slug' => 'green-data',
                'subject_id' => 10,
                'videoId' => 'DUFjMrIC1Is',
                'description' => 'Aqui, conceitos relacionados ao estudo dos processos de comunicação e de conexão na biosfera, tais como a Teoria de Gaia, servem ao estudo de exemplos de diferentes ecossistemas nos quais componentes ambientais (tais como a biodiversidade, a atmosfera e o clima) interagem com dispositivos tecnológicos (como sensores, dados, algoritmos etc.), transformando a comunicação, as relações e, por fim, as próprias estruturas das redes ecológicas formadas. Nossa espécie é apenas um dos elementos dessa ecologia, tanto quanto o são os dispositivos e os demais integrantes dessa rede ecossistêmica com os quais ela interage, compondo um novo modelo de cidadania.',
                'researchers' => ['Aline Pascoalino', 'Julliana Cutolo Torres', 'Rita Machado de Campos Nardy'],
                'background-image' => '/images/subjects/green-data_jacob-buchhave-unsplash.jpg',
                'background-credit' => 'jacob-buchhave/unsplash',
            ],
            [
                'name' => 'Jornalismos Possíveis, Mundos Possíveis',
                'image' => '/images/subjects/jornalismosPossiveis.jpg',
                'slug' => 'jornalismos-possiveis',
                'subject_id' => 20,
                'videoId' => 'k6Aq0goC-iM',
                'description' => 'A mirada aqui é a condução de uma epistemologia relacional rumo à construção de uma abordagem que denominamos Comunicação pelo Equívoco, uma operação comunicacional pela diferença, inspirada na tradução de mundos dos povos ameríndios. É a partir deste ponto que criamos o Observatório jornalismo(S), na Universidade Federal de Ouro Preto, onde lançamo-nos a imaginar jornalismos que traduzam mundos, sobretudo, a partir do encontro com a diferença (humanos e não humanos). Para isso damos continuidade às respectivas pesquisas, imaginando um “Jornalismo de Perspectivas” e colocando o “Jornalismo em Equívoco”.',
                'researchers' => ['Evandro José Medeiros Laia', 'Lara Linhalis Guimarães'],
                'background-image' => '/images/subjects/jornalismos-possiveis_waldemar-brandt-unsplash.jpg',
                'background-credit' => 'waldemar-brandt/unsplash',
            ],
            [
                'name' => 'Net-ativismo Indígena',
                'image' => '/images/subjects/net-ativismoIndigena.jpg',
                'slug' => 'net-ativismo-indigena',
                'subject_id' => 13,
                'videoId' => null,
                'description' => 'No Brasil, são mais de 250 povos ameríndios falantes de aproximadamente 160 idiomas. Esses povos estão nas redes, afirmam a sua dinamicidade cultural, na medida em que promovem a visibilidade dos conteúdos simbólicos de seus modos de existência. Além disso, a incorporação das tecnologias digitais por parte desses povos advém da percepção que a construção de novas estratégias comunicativas pode ajudar na pressão pela resolução de problemas históricos, como da garantia do direito à terra, à defesa dos territórios demarcados e à transmissão dos seus conhecimentos. De modo que essas questões envolvem igualmente a mobilização indígena, e não indígena, nesses ambientes informacionais digitais. Compreendemos essa atuação e protagonismo indígena como “net-ativismo", que envolve as características cosmopolíticas desses povos que possuem especificidades comunicativas e ecológicas próprias. A transversalidade do processo de digitalização designa a variação de sentidos da atuação indígena nas redes digitais associada aos seus modos de (re)existências e à agencialidade dos não humanos, correlatos às suas redes de relações.',
                'researchers' => ['Eliete Pereira', 'Thiago Cardoso Franco'],
                'background-image' => '/images/subjects/net-ativismo-indigena_deb-dowd-unsplash.jpg',
                'background-credit' => 'deb-dowd/unsplash',
            ],
            [
                'name' => 'Net-ativismo e Cultura Hacker',
                'image' => '/images/subjects/net-ativismoCulturaHacker.jpg',
                'slug' => 'net-ativismo-cultura-hacker',
                'subject_id' => 12,
                'videoId' => 'CrDBgTAASbQ',
                'description' => 'A partir da perspectiva da comunicação, buscamos aqui refletir sobre as novas formas de ação em rede constituídas em colaboração com as tecnologias digitais, incluindo as culturas hacker, nas quais as ações das entidades não humanas - como os vírus, os algoritmos, o big data - tornam-se cada vez mais evidentes na sociedade em que vivemos. Logo, abordamos o conceito de net-ativismo, os marcos históricos dos movimentos net-ativistas, as ecologias de interação em rede, dentre outros tópicos relacionados aos fenômenos de participação coletiva.',
                'researchers' => ['Marina Magalhães', 'Matheus Soares Cruz'],
                'background-image' => '/images/subjects/net-ativismo-cultura-hacker_tarik-haiga-unsplash.jpg',
                'background-credit' => 'tarik-haiga/unsplash',
            ],
            [
                'name' => 'Significados e Teorias das Novas Formas de Cidadania',
                'slug' => 'teorias-novas-formas-cidadania',
                'subject_id' => 15,
                'videoId' => 'aWbK1vRVQrQ',
                'description' => 'As transformações que interessam ao terceiro milênio não têm a ver apenas com as mudanças da nossa maneira de pensar e dos nossos hábitos. Muito mais interessa a alteração de nossa condição habitativa. As últimas gerações de tecnologias digitais, as mudanças climáticas e a pandemia contribuíram para a definitiva alteração da morfologia de nossa comunidade, mostrando a presença de novos atores no nosso convívio. O clima, as emissões, os vírus, mas também os algoritmos, os softwares, os dados, tornaram-se membros atuantes do nosso social, estendendo as fronteiras do mesmo para além dos muros da polis. Diante dessas mudanças, a ideia ocidental de sociedade baseada no contrato entre cidadãos e limitada ao convívio e à ação dos sujeitos humanos, assim como aquela de uma cidadania baseada apenas nos direitos fundamentais das pessoas, tornam-se inadequadas. A cidadania digital é hoje um âmbito de pesquisa transdisciplinar que se concentra sobre a superação do projeto político ocidental e o início de uma nova cultura da governança em redes complexas, caraterizadas por interações em arquiteturas não mais compostas de sujeito e de objeto. Objetivo deste verbetes é apresentar o debate, surgidos em diversos âmbitos disciplinares e as contribuições de diversos autores sobre os significados das transformações da ideia de cidadania.',
                'researchers' => ['Massimo Di Felice', 'Marcella Schneider Faria-Santos'],
                'background-image' => '/images/subjects/teorias-cidadania_josh-riemer-unsplash.jpg',
                'background-credit' => 'josh-riemer/unsplash',
            ],
            [
                'name' => 'Ontologias Contemporâneas e Cidadania Digital',
                'image' => '/images/subjects/ontologiasContemporaneas.jpg',
                'slug' => 'ontologias-contemporaneas',
                'subject_id' => 19,
                'videoId' => 'XbWxtCx-NCI',
                'description' => 'O referido verbete está relacionado a emergência de novas perspectivas ontológicas atentas ao contexto da cidadania digital, contrapondo-se às abordagens metafísicas e substancialistas que, durante séculos, contribuíram para uma definição fragmentada do agir político, do sujeito e da Natureza, dissociados entre si. No contexto em que nos debatemos com temas polêmicos, como engenharia genética, inteligência artificial, digitalização dos processos de informação, entre outros eventos, buscar-se-á através deste verbete contribuir para o debate e proposição de maneiras de pensar o humano, os objetos e a Natureza a partir da consideração das transformações propiciadas pelas tecnologias digitais no mundo contemporâneo.',
                'researchers' => ['Genivaldo Paulino Monteiro'],
                'background-image' => '/images/subjects/ontologias-contemporaneas_moritz-kindler-unsplash.jpg',
                'background-credit' => 'moritz-kindler/unsplash',
            ],
            [
                'name' => 'Pós-cidades e Redes de Cidadania',
                'image' => '/images/subjects/posCidades.jpg',
                'slug' => 'pos-cidades',
                'subject_id' => 14,
                'videoId' => 'k7DiWWQhWCQ',
                'description' => 'Aborda a experimentação, discussão e reflexão sobre a concepção de cidade e cidadania atualizadas pela digitalidade e conectividade, as quais alteram a nossa condição habitativa. Compreende a cidade como uma entidade viva, complexa, agente e comunicativa, um entrelaçamento das dimensões que lhe dão forma e entidades que a compõem (biológicas, físicas, digitais). Destes entrelaçamentos emerge a cidade híbrida - cibricidade - onde espaços hibridizados transformam a noção de cidadania para além da ideia de território geográfico e de relações identitárias entre grupos humanos. A constituição de redes de cidadania, ampliadas pelas digitalidade e conectividade, instauram formas ecológicas complexas de  habitação e comunicação. ',
                'researchers' => ['Eliane Schlemmer', 'Janaína Menezes'],
                'background-image' => '/images/subjects/pos-cidades_nasa-unsplash.jpg',
                'background-credit' => 'nasa/unsplash',
            ],
            [
                'name' => 'Medicina de Dados e Direito à Saúde Digital',
                'image' => '/images/subjects/medicinaDadosSaudeDigital.jpg',
                'slug' => 'medicina-dados',
                'subject_id' => 11,
                'videoId' => null,
                'description' => 'Com a seção dedicada à Saúde Digital, queremos refletir sobre as transformações que estão ocorrendo no contexto da saúde à luz dos processos de informação desencadeados pelas tecnologias digitais. Hoje, os processos de digitalização estão desafiando os rituais tradicionais de tratamento e diagnóstico por meio de ecossistemas terapêuticos informativos e transorgânicos gerados por dados, algoritmos e lógicas em rede. As mudanças em curso no mundo da saúde serão lidas no contexto de uma transformação cultural ditada pela transição da "medicina mecânica" para a "medicina informativa".',
                'researchers' => ['Silvia Surrenti'],
                'background-image' => '/images/subjects/Big-Data-Precision-Medicine.jpg',
                'background-credit' => '',
            ],
            [
                'name' => 'Transliteracia para Cidadania Digital',
                'image' => '/images/subjects/transliteraciasCidadania.jpg',
                'slug' => 'transliteracia-cidadania-digital',
                'subject_id' => 17,
                'videoId' => 'vks7jfUt2ic',
                'description' => 'Com o avanço cronológico da modernidade e pós-modernidade, o viés antropocêntrico da tecnologia foi colocado em xeque, congregando ao caráter social a ação de outros actantes não-humanos. Nesse contexto, a própria prática e pensamento sobre a comunicação se altera, já que partia do princípio e dos mecanismos transmissores de mensagens de humano para humano e passa a agregar outros focos de rede que fogem ao olhar humanista. Esta tendência acompanha o campo da educação, movimento que culminou na utilização de um outro termo para designação das habilidades do Século XXI: a palavra “literacia”. Algumas disparidades estão presentes na esfera do termo, que são frutos, também, da emergência do contemporâneo conectado em países com diferentes realidades linguísticas, econômicas, políticas e culturais. A discussão acerca do termo (entre alfabetização, letramento e literacia) é importante, para que sua articulação não seja inapropriada ou mesmo dicotômica, levando em consideração que o estudo do conceito tem como principal objetivo a capacidade de transmissão dos seus princípios e seu potencial de cidadania. A variedade de dimensões e leituras sobre essa perspectiva, contribuíram para a UNESCO expandir o termo de literacia de mídia e informação para o conceito de transliteracy (que pode ser traduzido como Transliteracia). A reboque da tecnologia emergente, a Internet se constitui nesse cenário como uma aparente extensão do mundo real e por isso as literacias digitais se associam com às transliteracias nesse cenário.',
                'researchers' => ['Beatrice Bonami Rosa'],
                'background-image' => '/images/subjects/transliteracia_kevin-ku-unsplash.jpg',
                'background-credit' => 'kevin-ku/unsplash',
            ],
        ];

        foreach ($subjectsToInsert as $subjectToInsert) {
            $subject = Subject::find($subjectToInsert['subject_id']);

            if(!$subject) {
                $subject = Subject::create([
                    'slug' => data_get($subjectToInsert, 'slug'),
                    'name' => data_get($subjectToInsert, 'name'),
                    'description' => data_get($subjectToInsert, 'description'),
                    'image' => data_get($subjectToInsert, 'image'),
                    'background-image' => data_get($subjectToInsert, 'background-image'),
                    'background-credit' => data_get($subjectToInsert, 'background-credit'),
                    'is_active' => data_get($subjectToInsert, 'is_active', true),
                ]);
            } else {
                $subject->update([
                    'slug' => data_get($subjectToInsert, 'slug'),
                    'description' => data_get($subjectToInsert, 'description'),
                    'image' => data_get($subjectToInsert, 'image'),
                    'background-image' => data_get($subjectToInsert, 'background-image'),
                    'background-credit' => data_get($subjectToInsert, 'background-credit'),
                ]);
            }


            // Inserindo os researchers
            $researchersToInsert = data_get($subjectToInsert, 'researchers');

            // alterando os usuários para inativos, para depois verificar se realmente devem ficar ativos no foreach!
            DB::table('subject_user')->where('subject_id', $subject->id)->update(['is_active' => false]);

            foreach ($researchersToInsert as $researcherToInsert) {
                $researcher = User::where('name', $researcherToInsert)->first();

                if(!$researcher) {
                    dd("O usuário {$researcherToInsert} não foi encontrado, por favor, realize o cadastro do mesmo!");
                }

                $existingUser = $subject->users->firstWhere('id', $researcher->id);

                if ($existingUser) {
                    $subject->users()->updateExistingPivot($existingUser->id, ['is_active' => true]);
                } else {
                    $now = \Carbon\Carbon::now();
                    $subject->users()->attach($researcher->id, ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
                }
            }


            // Inserindo os videos
            $video = data_get($subjectToInsert, 'videoId');

            if($video) {
                Video::create([
                    'subject_id' => $subject->id,
                    'video_id' => $video,
                    'is_featured' => true,
                    'is_active' => true,
                ]);
            }
        }
    }
}
