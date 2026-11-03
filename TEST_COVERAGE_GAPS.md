# Métodos inicialmente sem execução

Comparação em 03/11/2026 entre a suíte original (`ea9ca75`) e o primeiro lote de testes.

- Métodos sem nenhuma execução no início: **195**.
- Métodos desse grupo exercitados pelos novos testes: **72**.
- Métodos desse grupo ainda sem execução: **123**.

A classificação usa `count=0` nas entradas de método do Clover. Ela identifica métodos que nunca foram executados, não métodos executados parcialmente. Um método sair desta lista não significa que todas as suas linhas ou decisões estejam cobertas.

As linhas apontam para o código atual; as correções desta etapa não deslocaram os métodos listados.

## Métodos que passaram a ser exercitados

| Arquivo | Métodos |
| --- | --- |
| [app/Http/Controllers/GraphQLController.php](app/Http/Controllers/GraphQLController.php) | `query()` |
| [app/Http/Controllers/PostController.php](app/Http/Controllers/PostController.php) | `showUserPosts()`, `index()` |
| [app/Http/Controllers/SerieController.php](app/Http/Controllers/SerieController.php) | `index()`, `indexV2()` |
| [app/Http/Middleware/AiTokenMiddleware.php](app/Http/Middleware/AiTokenMiddleware.php) | `handle()` |
| [app/Models/Author.php](app/Models/Author.php) | `resources()` |
| [app/Models/Comment.php](app/Models/Comment.php) | `post()`, `user()` |
| [app/Models/PageItem.php](app/Models/PageItem.php) | `page()` |
| [app/Models/Post.php](app/Models/Post.php) | `getImageLinkAttribute()`, `isArchived()`, `comments()` |
| [app/Models/Resource.php](app/Models/Resource.php) | `getFormatDescription()`, `getFormatIcon()`, `getNewFormatIcon()`, `isTagSelected()`, `hasUploadedFile()`, `getUploadedFileOriginalName()`, `scopeGetByTagIds()` |
| [app/Models/Serie.php](app/Models/Serie.php) | `addVideo()`, `removeVideo()` |
| [app/Models/Sponsor.php](app/Models/Sponsor.php) | `series()` |
| [app/Models/SponsorLogo.php](app/Models/SponsorLogo.php) | `sponsor()` |
| [app/Models/Subject.php](app/Models/Subject.php) | `resources()`, `videos()`, `canBeExcluded()`, `isTagAssociated()` |
| [app/Models/Tag.php](app/Models/Tag.php) | `duplicateGroups()`, `series()`, `canBeExcluded()`, `scopeByName()`, `scopeByIds()`, `scopeActive()` |
| [app/Models/Ticket.php](app/Models/Ticket.php) | `getStatusText()`, `updatedBy()`, `scopeByCreatedUser()`, `scopeByResponsibleUser()`, `scopeByStatus()`, `canEdit()`, `canWork()`, `getTicketStatusCssClass()` |
| [app/Models/TicketComment.php](app/Models/TicketComment.php) | `createdBy()`, `ticket()` |
| [app/Models/University.php](app/Models/University.php) | `userInformations()`, `scopeActive()`, `scopeByName()`, `scopeByIds()` |
| [app/Models/UploadedFile.php](app/Models/UploadedFile.php) | `resource()`, `uploadedBy()` |
| [app/Models/User.php](app/Models/User.php) | `initials()`, `createdTickets()`, `receivedTickets()`, `videos()`, `getSlugAttribute()`, `scopeWithRole()`, `getCoordinators()`, `getResearchers()`, `getDesigners()`, `getDevelopers()`, `getUsersByRole()`, `getPersonMergedData()`, `getInvitedResearchers()`, `getSlugFrom()`, `comments()`, `series()` |
| [app/Models/UserInformation.php](app/Models/UserInformation.php) | `user()`, `getPictureLinkAttribute()` |
| [app/Models/Video.php](app/Models/Video.php) | `series()`, `createdBy()` |
| [app/Notifications/RegistrationRequested.php](app/Notifications/RegistrationRequested.php) | `toMail()` |
| [app/Notifications/WelcomeNewUser.php](app/Notifications/WelcomeNewUser.php) | `toMail()` |

## Métodos ainda sem execução

| Arquivo | Métodos |
| --- | --- |
| [app/Filament/FilamentResourceBase.php](app/Filament/FilamentResourceBase.php) | `mutateFormDataBeforeCreate()` |
| [app/Filament/Pages/Auth/RequestRegistration.php](app/Filament/Pages/Auth/RequestRegistration.php) | `getTitle()` |
| [app/Filament/Pages/EditProfile.php](app/Filament/Pages/EditProfile.php) | `form()`, `mutateFormDataBeforeFill()`, `mutateFormDataBeforeSave()` |
| [app/Filament/Resources/Concerns/DeleteAtBottom.php](app/Filament/Resources/Concerns/DeleteAtBottom.php) | `getBottomDeleteActions()` |
| [app/Filament/Resources/MapProjectResource.php](app/Filament/Resources/MapProjectResource.php) | `form()` |
| [app/Filament/Resources/MapProjectResource/Pages/EditMapProject.php](app/Filament/Resources/MapProjectResource/Pages/EditMapProject.php) | `getHeaderActions()`, `mutateFormDataBeforeSave()` |
| [app/Filament/Resources/MenuResource.php](app/Filament/Resources/MenuResource.php) | `table()` |
| [app/Filament/Resources/MenuResource/Pages/EditMenu.php](app/Filament/Resources/MenuResource/Pages/EditMenu.php) | `getBottomDeleteActions()`, `getRedirectUrl()` |
| [app/Filament/Resources/MenuResource/Pages/ListMenus.php](app/Filament/Resources/MenuResource/Pages/ListMenus.php) | `getHeaderActions()` |
| [app/Filament/Resources/PageResource.php](app/Filament/Resources/PageResource.php) | `table()` |
| [app/Filament/Resources/PageResource/Pages/EditPage.php](app/Filament/Resources/PageResource/Pages/EditPage.php) | `getBottomDeleteActions()`, `getRedirectUrl()` |
| [app/Filament/Resources/PageResource/Pages/ListPages.php](app/Filament/Resources/PageResource/Pages/ListPages.php) | `getHeaderActions()` |
| [app/Filament/Resources/PostResource.php](app/Filament/Resources/PostResource.php) | `table()` |
| [app/Filament/Resources/PostResource/Pages/ListPosts.php](app/Filament/Resources/PostResource/Pages/ListPosts.php) | `getHeaderActions()` |
| [app/Filament/Resources/ResourceResource.php](app/Filament/Resources/ResourceResource.php) | `table()` |
| [app/Filament/Resources/ResourceResource/Pages/ListResources.php](app/Filament/Resources/ResourceResource/Pages/ListResources.php) | `getHeaderActions()` |
| [app/Filament/Resources/SponsorResource/Pages/EditSponsor.php](app/Filament/Resources/SponsorResource/Pages/EditSponsor.php) | `afterSave()` |
| [app/Filament/Resources/SubjectResource/Pages/EditSubject.php](app/Filament/Resources/SubjectResource/Pages/EditSubject.php) | `getRedirectUrl()`, `mutateFormDataBeforeSave()` |
| [app/Filament/Resources/TagResource.php](app/Filament/Resources/TagResource.php) | `form()` |
| [app/Filament/Resources/TagResource/Pages/CreateTag.php](app/Filament/Resources/TagResource/Pages/CreateTag.php) | `getRedirectUrl()`, `mutateFormDataBeforeCreate()` |
| [app/Filament/Resources/TagResource/Pages/EditTag.php](app/Filament/Resources/TagResource/Pages/EditTag.php) | `getBottomDeleteActions()`, `getRedirectUrl()`, `mutateFormDataBeforeSave()` |
| [app/Filament/Resources/TicketResource/Pages/EditTicket.php](app/Filament/Resources/TicketResource/Pages/EditTicket.php) | `getTitle()`, `getBottomDeleteActions()`, `getRedirectUrl()`, `mutateFormDataBeforeSave()`, `handleRecordUpdate()` |
| [app/Filament/Resources/TicketResource/Pages/ManageTicketReplies.php](app/Filament/Resources/TicketResource/Pages/ManageTicketReplies.php) | `getTitle()`, `getBreadcrumb()`, `form()`, `infolist()`, `table()`, `shouldHiddeAction()` |
| [app/Filament/Resources/UniversityResource/Pages/CreateUniversity.php](app/Filament/Resources/UniversityResource/Pages/CreateUniversity.php) | `getRedirectUrl()` |
| [app/Filament/Resources/UniversityResource/Pages/EditUniversity.php](app/Filament/Resources/UniversityResource/Pages/EditUniversity.php) | `getBottomDeleteActions()`, `getRedirectUrl()` |
| [app/Filament/Resources/UserInformationResource/Pages/EditUserInformation.php](app/Filament/Resources/UserInformationResource/Pages/EditUserInformation.php) | `getBottomDeleteActions()` |
| [app/Filament/Resources/UserInformationResource/Pages/ListUserInformation.php](app/Filament/Resources/UserInformationResource/Pages/ListUserInformation.php) | `getHeaderActions()` |
| [app/Filament/Resources/VideoResource.php](app/Filament/Resources/VideoResource.php) | `table()` |
| [app/Filament/Resources/VideoResource/Pages/EditVideo.php](app/Filament/Resources/VideoResource/Pages/EditVideo.php) | `getBottomDeleteActions()` |
| [app/Filament/Resources/VideoResource/Pages/ListVideos.php](app/Filament/Resources/VideoResource/Pages/ListVideos.php) | `getHeaderActions()` |
| [app/Http/Controllers/Auth/VerifyEmailController.php](app/Http/Controllers/Auth/VerifyEmailController.php) | `__invoke()` |
| [app/Http/Controllers/HomeController.php](app/Http/Controllers/HomeController.php) | `index()`, `home()`, `bugReport()`, `helpRequest()`, `about()`, `aboutV2()`, `team()`, `teamV2()`, `landingPage()` |
| [app/Http/Controllers/MenuController.php](app/Http/Controllers/MenuController.php) | `index()`, `store()`, `show()`, `update()`, `destroy()`, `navigation()` |
| [app/Http/Controllers/ResourceController.php](app/Http/Controllers/ResourceController.php) | `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`, `searchByTag()`, `searchByTagV2()`, `searchBySubject()`, `searchBySubjectV2()`, `searchByFormat()`, `showAll()`, `showAllV2()`, `showByUser()`, `uploadImage()` |
| [app/Http/Controllers/SerieController.php](app/Http/Controllers/SerieController.php) | `show()` |
| [app/Http/Controllers/SubjectController.php](app/Http/Controllers/SubjectController.php) | `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`, `toggleStatus()`, `showSpecialPage()`, `showSpecialPageV2()` |
| [app/Http/Controllers/TagController.php](app/Http/Controllers/TagController.php) | `__construct()`, `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`, `toggleStatus()` |
| [app/Http/Controllers/TicketController.php](app/Http/Controllers/TicketController.php) | `index()`, `resourceReport()`, `show()`, `addComment()`, `edit()`, `editOwner()`, `editResponsible()`, `update()` |
| [app/Http/Middleware/TestMiddleware.php](app/Http/Middleware/TestMiddleware.php) | `handle()` |
| [app/Http/Resources/MenuResource.php](app/Http/Resources/MenuResource.php) | `toArray()` |
| [app/Models/Mail/NewRegister.php](app/Models/Mail/NewRegister.php) | `__construct()`, `build()` |
| [app/Models/Subject.php](app/Models/Subject.php) | `scopeSeries()`, `scopeSubjects()`, `findByConstant()`, `getSeriesSubjectMap()`, `getSubjectNamesImages()`, `getSubjectPagesContent()`, `getSeriesIdsFromSubjectId()` |
| [app/Models/User.php](app/Models/User.php) | `getWikiAccessToken()`, `getWikiUrl()`, `hasWikiAccess()` |

## Incompatibilidade identificada em Subject

`scopeSeries`, `scopeSubjects`, `findByConstant`, `getSeriesSubjectMap`, `getSubjectNamesImages`, `getSubjectPagesContent` e `getSeriesIdsFromSubjectId` dependem de `is_series`, `constant_key`, `series_mapping` ou `content_data`. Essas colunas não são criadas pelas migrations atuais. A tentativa de criar fixtures desses cenários falhou com `table subjects has no column named constant_key`; as consultas que usam esses campos também precisam ser reconciliadas com o esquema real.

Não foram criadas colunas artificiais nos testes nem adicionados testes ignorados para contabilizar esses métodos como cobertos. O próximo passo para esse grupo é decidir se essas funcionalidades devem ser restauradas com migrations e testes ou se o código ficou obsoleto. Os demais métodos pendentes precisam ser avaliados por seu uso e risco antes de escolher o próximo lote.
