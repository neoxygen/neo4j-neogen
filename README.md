# Neogen

[![Build Status](https://travis-ci.org/neoxygen/neo4j-neogen.svg?branch=master)](https://travis-ci.org/neoxygen/neo4j-neogen)

## Graph Generator for Neo4j

The library ease the generation of test graphs. The [faker](https://github.com/fzaninotto/faker) library is also used to generate random property values.

## This is in development

The library is in its early stage, pushed on Github to have feedbacks or requirements for other people using Neo4j with PHP.

A lot of work is currently done in order to have a start point, so changes are very likely to happen until the 1.0 version is released.

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

#### Properties parameters

Sometimes you'll maybe want to define some parameters for your properties, for e.g. to have a realistic date of birth for `Person` nodes,
you may want to be sure that the date will be between 50 years ago and 18 years ago if you set dob for people working for a company.

```yaml
nodes:
  persons:
    label: Person
    count: 10
    properties:
      firstname: firstName
      date_of_birth: { type: "dateTimeBetween", params: ["-50 years", "-18 years"]}

relationships:
    person_works_for:
        start: Person
        end: Company
        type: WORKS_AT
        mode: random
        properties:
            since: { type: "dateTimeBetween", params: ["-10 years", "now"]}
```

---

## Development

Contributions, feedbacks, requirements welcome. Shoot me by opening issues or PR or on twitter : [@ikwattro](https://twitter.com/ikwattro)

