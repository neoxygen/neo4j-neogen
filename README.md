# Neogen

## Graph Generator for Neo4j

The library ease the generation of test graphs. The [faker](https://github.com/fzaninotto/faker) library is also used to generate random property values.

## This is in development

The library is in its early stage, pushed on Github to have feedbacks or requirements for other people using Neo4j with PHP.

### Usage

#### Download the library :

```bash
git clone https://github.com/neoxygen/neogen

cd neogen
```

#### Define your testgraph schema :

```yaml
connection:
  scheme: http
  host: localhost
  port: 7474

nodes:
  persons:
    label: Person
    count: 50
    properties:
      firstname: firstName
      lastname: lastName

  companies:
    label: Company
    count: 10
    properties:
      name: company
      description: catchPhrase

relationships:
  person_works_for:
    start: Person
    end: Company
    type: WORKS_AT
    mode: 1

  friendships:
    start: Person
    end: Person
    type: KNOWS
    mode: random
```

#### Run the generate command :

```bash
vendor/bin/neogen generate
```

or you may want to export the generation queries to a file, handy for importing it in the Neo4j Console :

```bash
./vendor/bin/neogen generate --export="myfile.cql"
```

See the results in your graph browser !

#### Quick configuration precisions:

* When defining properties types (like company, firstName, ...), these types refers to the standard [faker](https://github.com/fzaninotto/faker) types.
* count define the number of nodes you want
* relationship mode : 1 for only one existing relationship per node, random for randomized number of relationships


---

## Development

Contributions, feedbacks, requirements welcome. Shoot me by opening issues or PR or on twitter : [@ikwattro](https://twitter.com/ikwattro)

