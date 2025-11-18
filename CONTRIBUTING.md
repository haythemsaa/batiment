# Guide de contribution

Merci de votre intérêt pour contribuer à BatiSaaS ! 🎉

## Comment contribuer

### Signaler un bug

Si vous trouvez un bug, veuillez créer une issue en incluant :
- Une description claire du problème
- Les étapes pour reproduire le bug
- Le comportement attendu vs le comportement actuel
- Votre environnement (OS, version PHP, MySQL, etc.)
- Des captures d'écran si pertinent

### Proposer une fonctionnalité

Pour proposer une nouvelle fonctionnalité :
1. Vérifiez qu'elle n'est pas déjà en cours de développement
2. Créez une issue décrivant :
   - Le besoin/problème à résoudre
   - La solution proposée
   - Des exemples d'utilisation
   - Les bénéfices pour les utilisateurs

### Soumettre une Pull Request

1. **Fork le projet**
```bash
git clone https://github.com/votre-username/batisaas.git
cd batisaas
```

2. **Créer une branche**
```bash
git checkout -b feature/ma-nouvelle-fonctionnalite
```

Conventions de nommage :
- `feature/` pour les nouvelles fonctionnalités
- `fix/` pour les corrections de bugs
- `docs/` pour la documentation
- `refactor/` pour le refactoring
- `test/` pour les tests

3. **Faire vos modifications**

Suivez les conventions de code du projet :
- PSR-12 pour le code PHP
- Commentaires en français
- Documentation des fonctions
- Code auto-documenté

4. **Tester vos modifications**

Assurez-vous que :
- Le code fonctionne correctement
- Il n'y a pas de régression
- Les nouvelles fonctionnalités sont testées

5. **Commit vos changements**

Format des messages de commit :
```
type(scope): description courte

Description détaillée si nécessaire

Closes #123
```

Types :
- `feat` : nouvelle fonctionnalité
- `fix` : correction de bug
- `docs` : documentation
- `style` : formatage, points-virgules manquants, etc.
- `refactor` : refactoring du code
- `test` : ajout de tests
- `chore` : maintenance

Exemple :
```bash
git commit -m "feat(devis): ajouter export Excel des devis"
```

6. **Push vers votre fork**
```bash
git push origin feature/ma-nouvelle-fonctionnalite
```

7. **Créer la Pull Request**

Dans la description :
- Décrivez les changements effectués
- Référencez les issues concernées
- Ajoutez des captures d'écran si pertinent
- Listez les points à vérifier

## Standards de code

### PHP

```php
<?php
/**
 * Description de la classe
 */
class MaClasse extends ClasseParent
{
    private $propriete;

    /**
     * Description de la méthode
     *
     * @param string $param Description du paramètre
     * @return mixed Description du retour
     */
    public function maMethode($param)
    {
        // Code ici
    }
}
```

### CSS

```css
/* Commentaire de section */
.ma-classe {
    /* Propriétés par ordre alphabétique */
    background-color: #fff;
    border: 1px solid #ccc;
    color: #333;
    display: flex;
}
```

### JavaScript

```javascript
/**
 * Description de la fonction
 * @param {string} param - Description
 * @returns {void}
 */
function maFonction(param) {
    // Code ici
}
```

## Directives de développement

### Architecture

- Respecter le pattern MVC
- Un contrôleur = une responsabilité
- Modèles minces, contrôleurs épais
- Pas de logique métier dans les vues

### Sécurité

- Toujours utiliser des requêtes préparées
- Valider et nettoyer toutes les entrées utilisateur
- Échapper les sorties HTML
- Utiliser CSRF tokens pour les formulaires
- Ne jamais stocker de mots de passe en clair

### Performance

- Minimiser les requêtes SQL
- Utiliser des index sur les colonnes fréquemment recherchées
- Optimiser les images
- Mettre en cache quand c'est possible

### Base de données

- Préfixer les tables si nécessaire
- Utiliser des migrations pour les changements de schéma
- Documenter les colonnes importantes
- Utiliser des foreign keys pour l'intégrité

## Documentation

Toute nouvelle fonctionnalité doit être documentée :
- Commentaires dans le code
- Mise à jour du README si nécessaire
- Exemples d'utilisation
- Guide utilisateur si pertinent

## Tests

Avant de soumettre une PR :
- [ ] Le code fonctionne en local
- [ ] Aucune erreur PHP
- [ ] Pas de warnings dans la console
- [ ] Compatible avec PHP 8.0+
- [ ] Fonctionne sur Chrome, Firefox, Safari
- [ ] Responsive (mobile, tablette, desktop)

## Code de conduite

### Nos engagements

En participant à ce projet, nous nous engageons à :
- Être respectueux envers tous les contributeurs
- Accepter les critiques constructives
- Se concentrer sur ce qui est le mieux pour la communauté
- Faire preuve d'empathie

### Comportements inacceptables

- Langage ou images sexualisés
- Commentaires insultants ou dégradants
- Harcèlement public ou privé
- Publication d'informations privées sans permission
- Autres conduites contraires à l'éthique professionnelle

## Questions

Pour toute question :
- Créez une issue avec le tag `question`
- Contactez-nous à support@batisaas.com
- Rejoignez notre Discord (lien à venir)

## Licence

En contribuant, vous acceptez que vos contributions soient sous licence MIT.

---

Merci pour votre contribution ! 🙏
