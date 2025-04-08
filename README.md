# FoodMenu - PHP WebService API
## Members
- Simon Kalmi Claesson (@sbamboo)
- Arvid Ersson (@Astikornipal)
- Otis Gustafsson (@bumblebroo)

*NTI Gymnasiet Skövde - 2025 - TE3s*
<br><br>

## Code assumptions
In `/index.php` the latest-api-version is assumed when defining the *servive* url!<br>
In `/latest/index.php` the latest-api-version is assumed when defining the redirect!<br>
In `/docs/index.php` the latest-api-version is assumed when setting `defaultVersion`!<br>
<br><br>

## Planning
Project planning can be found in `/.dev/PLANNING.md`
<br><br>

## Project Structure
In the project root the main home page for the api is avaliable in `/index.php` aswell as any required css for that page in `/index.css`.

Project level assets like font deffinitions are also in the project root, in this case `/fonts.css`.

The folder `/docs` contains the code and assets for the document viewer website, the documents themself are markdown files in the same folder, for example `v1.md` for the 1.0 documentation.
Note the docs site can be filtered for a specific version using the `?ver=` url-param. If no param is used or the supplied value is invalid the latest is used. *(Currently which version to default to is manuall set)*

The other folders *(except those begining with .)* are for the different api versions, example `/v1`.

There is also the folder `/latest` which redirects to the latest api version to aid in routing. *(Currently this is manually set)*