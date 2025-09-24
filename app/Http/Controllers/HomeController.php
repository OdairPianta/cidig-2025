<?php

namespace App\Http\Controllers;

use App\Models\Mail\ReportBug;
use App\Models\Resource;
use App\Models\Subject;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public const CONGRESS_URL = 'https://eventos.ifsc.edu.br/cidig';

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index()
    {
        $series = [];
        foreach (Subject::SERIES_PAGES as $seriesId => $seriesData){
            if ($seriesId === Subject::UNICO) {
                continue;
            }
            $series[] = [
                'id' => $seriesId,
                'description' => $seriesData['description'],
                'tags' => Tag::byIds($seriesData['tags_ids'])->get(),
                'thumbnail' => url($seriesData['thumbnail']),
                'url' => route('showSpecialPage', $seriesId),
            ];
        }

        $subjects = array_filter(
            Subject::SUBJECT_NAMES_IMAGES,
            function ($subject) {
                return strpos($subject['image'], 'subjects') > 0;
            }
        );

        $partners = [
            url('/images/logos/fapcom-logo.png'),
            url('/images/logos/jornalismos-logo.png'),
            url('/images/logos/fake-news-logo.png'),
            url('/images/logos/gpe-du-logo.png'),
        ];

        return view('home', compact('subjects', 'series', 'partners'));
    }

    public function indexV2()
{

    $series = [];
    $tagsToShow = [];
    
    $seriesRecords = \App\Models\Serie::where('is_active', true)
        ->with(['tags' => function($query) {
            $query->where('is_active', true);
        }])
        ->get();
    
    foreach ($seriesRecords as $serieRecord) {
        if ($serieRecord->slug === 'unico') {
            continue;
        }
        
        $tagsToShow = array_merge($tagsToShow, $serieRecord->tags->toArray());
        
        $series[] = [
            'id' => $serieRecord->slug,
            'title' => $serieRecord->title,
            'description' => $serieRecord->description,
            'tags' => $serieRecord->tags,
            'thumbnail' => url($serieRecord->thumbnail_novo ?? $serieRecord->thumbnail ?? '/images/default.jpg'),
            'url' => route('v2.showSpecialPage', $serieRecord->slug),
        ];
    }

    $subjects = Subject::where('is_active', true)
    ->whereNotNull('image')
    ->where('image', '!=', '')
    ->get()
    ->mapWithKeys(function ($subject) {
        return [
            $subject->slug => [
                'name' => $subject->name,
                'image' => url($subject->image),
                'id' => $subject->slug,
            ]
        ];
    })
    ->toArray();

    $partners = [
        url('/images/logos/fapcom-logo-gray.png'),
        url('/images/logos/jornalismos-logo-gray.png'),
        url('/images/logos/fake-news-logo-gray.png'),
        url('/images/logos/gpe-du-logo-gray.png'),
    ];

    $masterclasses = [
        [
            'title' => 'Evento Cidig',
            'url' => self::CONGRESS_URL,
            'thumbnail' => '/images/events/cidig_bannerWeb.jpg',
            'thumbnail-tablet' => '/images/events/cidig_bannerTablet.jpg',
            'thumbnail-mobile' => '/images/events/cidig_bannerMobile.jpg',
        ],
    ];

    return view('v2/home',
        compact(
            'subjects',
            'series',
            'partners',
            'tagsToShow',
            'masterclasses'
        )
    );
}

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home()
    {
        if (!auth()->check()){
            return redirect('/');
        }

        $tickets = Ticket::query()
            ->byResponsibleUser(auth()->user())
            ->byStatus([Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $resources = Resource::where('created_by',auth()->id())
            ->with('subject', 'user')
            ->paginate(20);

        return view('resources.index', compact('resources', 'tickets'));
    }

    public function bugReport(Request $request)
    {
        Mail::to(env('MAIL_FROM_ADDRESS'))
            ->cc('sandrogallina1984@gmail.com')
            ->send(new ReportBug(auth()->user(), 'Erro', $request->get('description')));

        return back();
    }

    public function helpRequest(Request $request)
    {
        Mail::to(env('MAIL_FROM_ADDRESS'))
            ->cc('sandrogallina1984@gmail.com')
            ->send(new ReportBug(auth()->user(), 'Ajuda', $request->get('description')));

        return back();
    }

    public function about()
    {
        return view('pages.about');
    }

    public function aboutV2()
    {
        $coordinators = [
            'title' => 'Coordenador Científico',
            'people' => User::getCoordinators()
        ];

        $researchers = [
            'title' => 'Professores e Pesquisadores',
            'people' => User::getResearchers(),
        ];

        $invitedResearchers = [
            'title' => 'Atopos Internacional',
            'people' => User::getInvitedResearchers(),
        ];

        $researchers = [
            'title' => 'Demais integrantes',
            'people' => $researchers['people']->merge($invitedResearchers['people']),
        ];

        return view('v2.pages.about', compact('researchers', 'coordinators'));
    }

    public function team(bool $newVersion = false)
    {
        $coordinators = [
            'title' => 'Coordenador Científico',
            'people' => User::getCoordinators()
        ];

        $researchers = [
                'title' => 'Professores e Pesquisadores',
                'people' => User::getResearchers(),
            ];
        $designers = [
                'title' => 'UI/UX Designer',
                'people' => User::getDesigners(),
            ];
        $developers = [
                'title' => 'Desenvolvedor Full-stack',
                'people' => User::getDevelopers(),
            ];
        $invitedResearchers = [
            'title' => 'Atopos Internacional',
            'people' => User::getInvitedResearchers(),
        ];

        if ($newVersion) {
            $researchers = [
                'title' => 'Professores e Pesquisadores',
                'people' => $researchers['people']->merge($invitedResearchers['people']),
            ];

            return view('v2.pages.team', compact('researchers', 'coordinators'));
        }

        return view('pages.team', compact('coordinators', 'researchers', 'designers', 'developers', 'invitedResearchers'));
    }

    public function teamV2()
    {
        return $this->team(true);
    }


    public function landingPage()
    {
        return view('lp');
    }
}
