<?php

namespace common\components\notify;

use b2b\modules\api\models\RecoveryForm;
use b2b\modules\api\models\RegistrationForm;
use Exception as ExceptionAlias;
use Yii;

class UserNotify extends Notify
{

	/**
	 * @param RegistrationForm $registration
	 * @return bool
	 * @throws ExceptionAlias
	 */
	public function registration(RegistrationForm $registration)
	{

		return $this->sendEmail(Yii::$app->params['notify.email']['user.registration'], 'Новая заявка на регистрацию в сервисе B2B - Продажа шин', 'user/registration', [
			'registration' => $registration,
			'sayings' => $this->getSayingsString(),
		], Yii::$app->params['notify.email']['send.from']);
	}

	/**
	 * @param RecoveryForm $recovery
	 * @return bool
	 * @throws ExceptionAlias
	 */
	public function recovery(RecoveryForm $recovery)
	{
		return $this->sendEmail(Yii::$app->params['notify.email']['user.recovery'], 'Новая заявка на восстановление доступа к сервису B2B - Продажа шин', 'user/recovery', [
			'form' => $recovery,
			'sayings' => $this->getSayingsString(),
		], Yii::$app->params['notify.email']['send.from']);
	}

	/**
	 * @return mixed
	 * @throws ExceptionAlias
	 */
	protected function getSayingsString()
	{

		$options = static::getSayingsOptions();
		return $options[random_int(0, \count($options) - 1)];
	}

	static protected function getSayingsOptions()
	{
		return [
			"Аво́сь да как-нибу́дь до добра́ не доведу́т.	Draw not your bow till your arrow is fixed.	 Literal: Maybe and somehow won't make any good.",
			"Азбука -- к мудрости ступенька.	You have to learn to walk before you can run.	 Literal: Alphabet is the step to wisdom.",
			"Алты́нного во́ра ве́шают, а полти́нного че́ствуют.	Little thieves are hanged, but great ones escape.	 Literal: The thief who stole an altyn (3 kopecks) is hung, and the one who stole a poltinnik (50 kopecks) is praised.",
			"Аппети́т прихо́дит во вре́мя еды́.	Appetite comes with eating.	 Literal: The appetite comes during eating.",
			"Арте́льный горшо́к гу́ще кипи́т.	With a helper a thousand things are possible.	 Literal: An artel's pot boils denser.",
			"Ахал бы дядя, на себя глядя.	The devil rebuking sin.	 Literal: The uncle would better gasp looking at himself.",
			"Ба́ба с во́зу -- кобы́ле ле́гче.	No women, no cry!	 Literal: It is easier for the mare when a woman gets off the cart.",
			"Ба́бушка гада́ла, на́двое сказа́ла.	No one can know for certain.	 Literal: Granny was telling fortunes, said two things.",
			"Ба́бушка на́двое сказа́ла.	No one can know for certain.	 Literal: Granny said two things.",
			"Ба́ры деру́тся -- у холо́пов чубы́ треща́т.	When the rich make war it's the poor that die.	 Literal: Masters are fighting, servants' forelocks are creaking.",
			"Беда́ никогда́ не прихо́дит одна́.	When it rains, it pours.	 Literal: Trouble never comes alone.",
			"Бедному нужно многое, жадному -- всё.	Poverty is in want of much, avarice of everything.	",
			"Бе́дному оде́ться -- то́лько подпоя́саться.	Не that has no money needs no purse.	 Literal: For a poor man, to dress means to only belt himself.",
			"Без кота́ мыша́м раздо́лье.	When the cat is away, the mice will play.	 Literal: Without a cat mice feel free.",
			"Без муки нет науки.	Adversity is a good teacher.	 Literal: Without torture no science.",
			"Без отдыха и конь не скачет.	All work and no play makes Jack a dull boy.	 Literal: Without rest even the horse doesn't gallop.",
			"Без труда́ не вы́тащишь и ры́бку из пруда́.	No pain, no gain.	 Literal: Without effort, you can't even pull a fish out of the pond.",
			"Безопасность прежде всего.	Safety first.	",
			"Береги́ пла́тье сно́ву, а честь смо́лоду.	Look after your clothes when they're spick and span, and after your honour when you're a young man.	",
			"Бережёного Бог бережёт.	The Lord helps those who help themselves.	 Literal: God keeps those safe who keep themselves safe.",
			"Бережливость хороша, да скупость страшна.	Economy is a good servant but a bad master.	",
			"Бери́сь дру́жно, не бу́дет гру́зно.	Many hands make light work	 Literal: Take hold of it together, it won't feel heavy.",
			"Бе́шеной соба́ке семь вёрст не крюк.	 Literal: For a mad dog, seven versts (Russian mile) is not a long detour.	",
			"Близо́к локото́к, да не уку́сишь.	So near and yet so far.	 Literal: Your elbow is close, yet you can't bite it.",
			"Бог дал, Бог и взял.	The Lord giveth and the Lord taketh away	 Literal: God gave, God took back.",
			"Бог не вы́даст, свинья́ не съест.	 Literal: God won't give it away, pigs won't eat it.	",
			"Бог плу́та ме́тит.	 Literal: God marks the crook.	",
			"Бог правду видит, да не скоро скажет.	The mills of God grind slowly.	 Literal: God sees the truth, but won't tell soon.",
			"Бог правду видит.	There is One that is always on the lookout.	 Literal: God sees the truth.",
			"Бог тро́ицу лю́бит.	Third time is a charm.; Third time is a lucky.	 Literal: God likes trinity.",
			"Бо́гу моли́сь, а добра́-ума́ держи́сь.	Trust in God, but steer away from the rocks.	 Literal: Pray to God, but hold on to your good mind.",
			"Бодли́вой коро́ве Бог рог не даёт.	God does not give horns to cow that butts.	",
			"Болту́н -- нахо́дка для шпио́на.	Loose lips sink big ships.	 Literal: A chatterbox is a treasure for a spy.",
			"Болтуна язык до добра не доведёт.	A fool's tongue runs before his feet.	 Literal: The tongue will bring the chatterer no good.",
			"Больше слушай, меньше говори.	Be swift to hear, slow to speak.	 Literal: Listen more, talk less.",
			"Большой секрет -- знает весь свет.	Every barber knows that. (badly-kept secret)	 Literal: Big secret -- all the world knows.",
			"Большо́му кораблю́ -- большо́е пла́вание.	A great ship needs deep waters.	 Literal: For a big ship, a big voyage.",
			"Борода не делает философом.	A beard doesn't make a philosopher.	",
			"Брань на во́роте не ви́снет.	Sticks and stones may break my bones, but words will never hurt me.	 Literal: The scolding won't hang on one's collar.",
			"Брань не дым -- глаза не ест.	Hard words break no bones.	 Literal: The scolding is not smoke -- won't irritate your eyes.",
			"Брюхо сыто, да глаза голодны.	His eyes are bigger than his belly.	 Literal: The belly is full, but the eyes are hungry.",
			"Бу́дет и на на́шей у́лице пра́здник.	There will be our turn to triumph.	 Literal: There'll be a holiday in our street too.",
			"Будь что будет.	Be that as it may.	 Literal: Be what will be.",
			"Бума́га всё сте́рпит.	A letter does not blush (Epistula non erubescit -- Cicero, Epistulae ad familiares)	 Literal: Paper will endure anything.",
			"Была́ не была́.	Whatever betide.	 Literal: There was -- there wasn't.",
			"Быть в доме хозяином.	To rule the roost.	 Literal: To be a host in the house.",
			"В глаза льстит, а за глаза пакостит.	To carry fire in one hand and water in the other.	",
			"В гостя́х хорошо́, а до́ма лу́чше.	There's no place like home.; East or West, home is best.	 Literal: It is good to be visiting, but it is better at home.",
			"В каждой шутке есть доля правды.	Many a true word is spoken in jest.	",
			"В кулаке все пальцы равны.	Teeth are all friends among each other.	",
			"В нога́х правды не́т.	Take a seat, please.	 Literal: There is no truth in feet.",
			"В огоро́де бузина́, а в Ки́еве дя́дька.	Red herring.	 Literal: Elder-berry is in the kitchen-garden, and the uncle is in Kiev.",
			"В родном углу всё по нутру.	It is good to be visiting, but it is better at home.	",
			"В семье́ не без уро́да.	Every family has its black sheep.	 Literal: No family has no ugly member.",
			"В темноте все кошки серы.	All cats are grey in the dark.	",
			"В тесноте́, да не в оби́де.	The more the merrier.	 Literal: In a crush, yet without resentment.",
			"В ти́хом о́муте че́рти во́дятся.	Still waters run deep.	 Literal: It's the still waters that are inhabited by devils.",
			"В Ту́лу со свои́м самова́ром не е́здят.	When in Rome, do as Romans do.	 Literal: Nobody goes to Tula with one's own samovar. (Tula is famous as city where the best Russian samovars are made)",
			"В чужо́й монасты́рь со свои́м уста́вом не хо́дят.	When in Rome, do as Romans do.	 Literal: Nobody goes to another monastery with one's own charter.",
			"В чужо́м глазу́ сори́нку заме́тно, а в своём -- бревна́ не вида́ть.	And why beholdest thou the mote that is in thy brother's eye, but perceivest not the beam that is in thine own eye? (Luke 6:41)	 Literal: In another persons' eye one can notice a mote, but in one's own - cannot see a log.",
			"В чужу́ю жену́ чёрт ло́жку мёда кладёт.	The devil puts a touch of honey in a neighbor's wife.	 Literal: The devil puts a spoonful of honey into others' wife.",
			"Важно не то, как долго ты прожил, а как хорошо жил.	How well you live makes a difference, not how long.	",
			"Вали́ на се́рого, се́рый всё свезёт.	 Literal: Put everything onto the grey horse, he'll bear anything.	",
			"Вашими уста́ми, да мёд пить.	It is too good to be true.	 Literal: I'd like to drink honey with your lips.",
			"Век живи́ -- век учи́сь.	Live and learn.	 Literal: Live for a century -- learn for a century.",
			"Велик местом, а говорить не с кем.	Better fed than taught.	",
			"Вели́к те́лом, да мал де́лом.	Penny wise and pound foolish.	 Literal: Big of the body but small by his deeds.",
			"Вертит языком, что корова хвостом.	As garrulous as a magpie.	 Literal: He twirls his tongue as the cow twirls its tail.",
			"Весело́ весе́лье -- тяжело́ похме́лье.		 Literal: Revelry is jolly, hangover is heavy.",
			"Взя́лся за гуж -- не говори́, что не дюж.	If you pledge, don't hedge.	 Literal: When taking the tug do not say \"I am powerless\".",
			"Ви́дит о́ко, да зуб неймёт.	Eyes watch but cannot take.; So near and yet so far.	 Literal: The eye can see it, but the tooth can't bite it.",
			"Видна́ пти́ца по полёту.	A bird may be known by its song.	 Literal: The bird is known by its flight.",
			"Видно мастера по работе.	The work shows the workman.	",
			"Ви́лами на воде́ пи́сано.	Nobody knows whether it will happen or not.	 Literal: This is written with pitchfork on a flowing water.",
			"Вино́ вину́ твори́т.	When wine is in, wit is out.	 Literal: Wine causes guilt.",
			"Вкру́те и вяз перело́мишь		 Literal: In affect you can break even an elm.",
			"Вме́сте те́сно, а врозь ску́чно.	You can't live with them and you can't live without them.	 Literal: Together, it's cramped; apart, it's boring.",
			"Вода́ ка́мень то́чит.	Little strokes fell great oaks.	 Literal: Water cuts through stone.",
			"Волк в ове́чьей шку́ре.	Wolf in sheep's clothing.	 Literal: Wolf in sheep's pelt.",
			"Во́лка но́ги ко́рмят.	A hound's food is in its legs.	 Literal: The feet feed the wolf.",
			"Волко́в боя́ться -- в лес не ходи́ть.	If you can't stand the heat, stay out of the kitchen.	 Literal: If you're afraid of wolves, don't go to the woods.",
			"Волков бояться -- дров не иметь.	Being afraid of wolfs do not go to the forest.	",
			"Вор у во́ра дуби́нку укра́л.	There is no honor among thieves.	 Literal: A thief stole other thief's club.",
			"Во́рон во́рону глаз не вы́клюет.	Hawks will not pick out hawk's eye.	 Literal: The raven will not peck another raven's eye.",
			"Вору потакать -- что самому воровать.	The receiver is as bad as the thief.	",
			"Вот в чём загво́здка.	That's where the shoe pinches; That's the crux.	 Literal: That's the snag!",
			"Вот где соба́ка зары́та.	That's where the shoe pinches; That's the crux.	 Literal: That's where the dog is buried.",
			"Во́т тебе, ба́бушка, и Ю́рьев де́нь.	What an unpleasant surprise!	 Literal: That's, grandma, the Yuri's Day.",
			"Временами и дурак умно говорит.	Fools may sometimes speak to the purpose.	",
			"Вре́мя -- лу́чший до́ктор	Time heals all wounds.	 Literal: Time is the best healer.",
			"Врёт -- и глазом не мигнёт.	He lies easily and without blushing.	",
			"Ври, да помни.	A liar should be a man of good memory.	 Literal: Lie but remember.",
			"Всё в руках божьих.	God's hand is above all.	 Literal: Everything is in God's hands.",
			"Всё гениальное просто.	Genius is simplicity.	 Literal: Everything genius is simple.",
			"Все дороги ведут в Рим.	All roads lead to Rome.	",
			"Все меняется, ничто не исчезает.	Nothing disappears, only changes.	 Literal: Everything changes, nothing disappears.",
			"Все на солнце ровно глядим -- неровно пьём и едим.	We all see the same sun, but we don't all have the same fun.	",
			"Всё хорошо в меру.	Everything in reason.	 Literal: Everything is good in measure.",
			"Всё хорошо́, что хорошо́ конча́ется.	All's well that ends well.	 Literal: All is well that ends well.",
			"Всего́ с собо́й не унесёшь.	The one who dies with most toys, still dies.	 Literal: You can't take everything with you.",
			"Всему своё место.	There is a place for everything, and everything in its place.	",
			"Всяк глядит, да не всяк видит.	Everything has beauty but not everyone sees it.	",
			"Всяк кули́к своё боло́то хва́лит.	Every cook praises his own broth.	 Literal: Every sandpiper praises his own swamp.",
			"Всяк сверчо́к знай свой шесто́к.	Every man to his business.; What Jupiter is allowed to do, cattle are not.	 Literal: Every cricket must know its hearth",
			"Вся́кому о́вощу своё вре́мя.	Everything is good in its season.	 Literal: Every vegetable has its time.",
			"Вы́ше головы́ не пры́гнешь.	You can not jump above your head.	",
		];
	}

}
