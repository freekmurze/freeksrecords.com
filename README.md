# Freek's Records

[![tests](https://github.com/freekmurze/freeksrecords.com/actions/workflows/tests.yml/badge.svg)](https://github.com/freekmurze/freeksrecords.com/actions/workflows/tests.yml)

The source of [freeksrecords.com](https://freeksrecords.com), a place to browse my vinyl collection. Records sit on a walnut shelf in a listening corner. Pull one out, read the (slightly worn) sleeve notes, and jump to any track on Spotify or Apple Music.

The collection is synced from [Discogs](https://www.discogs.com/user/freekmurze/collection). Every record and the collection as a whole get their own shareable URL with a generated social preview image.

## Why this exists

I've always loved music. When I was a student, I used to buy vinyl records and really enjoyed playing them. In the iPod era, I stopped getting vinyl because having your music always with you was just so nice.

Flash forward 15 to 20 years. Everything is digitized, and AI is sweeping the planet. Because I interact with the digital world so much, I value physical things again and enjoy doing stuff without involving computers.

So in the past year, I started collecting vinyl again, and I very much enjoy putting a record on each and every night I'm home. It has nothing to do with sound quality, but with the experience of looking through the collection, picking a record, holding it... making a conscious decision.

A couple of friends were asking for music suggestions, so I decided to create a nice site that lists my records so I can share it with them. I was already tracking my collection with Discogs, so the data comes from that API.

The site is made with Laravel, but I didn't write a single line of it. It was mainly an experiment in how easily an AI could build this and deploy it on [Laravel Cloud](https://cloud.laravel.com) (spoiler: it was very easy, and I was impressed).

All of this was done while listening to:

- _Shebang_ by Oren Ambarchi
- _How You Been_ by SML
- _Eureka_ by Jim O'Rourke

Happy browsing!

## Stack

- [Laravel 13](https://laravel.com) on PHP 8.5
- [Inertia v3](https://inertiajs.com) with React 19 and TypeScript
- [Tailwind CSS v4](https://tailwindcss.com)
- [Wayfinder](https://github.com/laravel/wayfinder) for typed routes on the frontend
- GD for rendering the Open Graph images
- [Vite+](https://viteplus.dev) for building, linting and formatting
- Pest, PHPStan (Larastan) and Pint

## Running it locally

```bash
git clone https://github.com/freekmurze/freeksrecords.com.git
cd freeksrecords.com
composer setup
composer dev
```

`composer setup` installs the PHP and JS dependencies, creates `.env`, generates an app key, migrates the SQLite database and builds the frontend. The site works straight away using the bundled snapshot in `resources/data/collection.json`.

## Syncing a collection

Point the app at a Discogs user in `.env`:

```dotenv
DISCOGS_USERNAME=freekmurze
DISCOGS_TOKEN=your-personal-access-token
```

A token is optional, but raises the Discogs rate limit considerably. Then use these commands:

| Command                            | What it does                                                              |
| ---------------------------------- | ------------------------------------------------------------------------- |
| `records:sync-discogs`             | Import new additions and remove records that left the collection          |
| `records:sync-dates --musicbrainz` | Store original and pressing dates, looking up missing ones on MusicBrainz |
| `records:repair-tracklists`        | Group movements that share a physical track                               |
| `records:copy-covers`              | Copy cover art into the public object storage disk                        |
| `records:export-snapshot`          | Export the collection and verified dates to the bundled snapshot          |
| `records:import-snapshot`          | Import the bundled snapshot into the configured database                  |

## Testing

```bash
composer test      # Pint, PHPStan and Pest
composer ci:check  # everything above, plus `vp check` and tsc
```

## License

The code is open source under the MIT license. Album artwork belongs to its respective owners.
