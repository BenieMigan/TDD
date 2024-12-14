<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use App\Models\Like;
use App\Models\Chirp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChirpTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
   

    public function test_un_utilisateur_peut_creer_un_chirp()
    {
        // Simuler un utilisateur connecté
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
            'message' => 'Mon premier chirp !'
        ]);

        // Vérifier que le chirp a été ajouté à la base de données
        $reponse->assertStatus(201); // Assurer que la méthode store retourne 201
        $this->assertDatabaseHas('chirps', [
            'message' => 'Mon premier chirp !',
            'user_id' => $utilisateur->id,
        ]);
    }

    public function test_un_chirp_ne_peut_pas_avoir_un_contenu_vide()
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
            'message' => ''
        ]);

        $reponse->assertSessionHasErrors(['message']);
    }

    public function test_un_chirp_ne_peut_pas_depasse_255_caracteres()
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
            'message' => str_repeat('a', 256)
        ]);

        $reponse->assertSessionHasErrors(['message']);
    }

    public function test_les_chirps_sont_affiches_sur_la_page_d_accueil()
    {
        $chirps = Chirp::factory()->count(3)->create();
        $reponse = $this->get('/');
        foreach ($chirps as $chirp) {
            $reponse->assertSee($chirp->message);
        }
    }
    public function test_un_utilisateur_peut_modifier_son_chirp()
{
 $utilisateur = User::factory()->create();
 $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
 $this->actingAs($utilisateur);
 $reponse = $this->put("/chirps/{$chirp->id}", [
 'message' => 'Chirp modifié'
 ]);
 $reponse->assertStatus(302);
 // Vérifie si le chirp existe dans la base de donnée.
 $this->assertDatabaseHas('chirps', [
 'id' => $chirp->id,
 'message' => 'Chirp modifié',
 ]);
}

public function test_un_utilisateur_peut_supprimer_son_chirp
()
{
 $utilisateur = User::factory()->create();
 $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
 $this->actingAs($utilisateur);
 $reponse = $this->delete("/chirps/{$chirp->id}");
 $reponse->assertStatus(302);
 $this->assertDatabaseMissing('chirps', [
    'id' => $chirp->id,
    ]);
   }
    public function test_utilisateur_2_ne_peut_pas_modifier_ni_supprimer_le_chirp_de_utilisateur_1()
    {
        // Créer deux utilisateurs et un chirp pour l'utilisateur 1
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $user1->id]);

        // Tenter de modifier le chirp avec utilisateur 2
        $this->actingAs($user2);
        $response = $this->put("/chirps/{$chirp->id}", [
            'message' => 'Attempted change by user 2',
        ]);
        $response->assertStatus(403);

        // Tenter de supprimer le chirp avec utilisateur 2
        $response = $this->delete("/chirps/{$chirp->id}");
        $response->assertStatus(403);
    }
   
    public function test_contenu_vide_n_est_pas_accepte_lors_de_la_mise_a_jour()
    {
        $user = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->put("/chirps/{$chirp->id}", [
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['message']);
    }

    public function test_contenu_trop_long_n_est_pas_accepte_lors_de_la_mise_a_jour()
    {
        $user = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->put("/chirps/{$chirp->id}", [
            'message' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors(['message']);
    }
 

    public function test_un_utilisateur_ne_peut_pas_creer_un_11e_chirp()
    {
        $user = User::factory()->create();

        // Créer 10 chirps pour l'utilisateur
        Chirp::factory()->count(10)->create(['user_id' => $user->id]);

        // Simuler l'authentification de l'utilisateur
        $this->actingAs($user);

        // Tenter de créer un 11e chirp
        $response = $this->post('/chirps', [
            'message' => 'Ceci est le 11e chirp',
        ]);

        // Vérifier que la création échoue avec une erreur appropriée
        $response->assertSessionHasErrors(['message' => 'Vous avez atteint la limite de 10 chirps.']);
    }

    public function test_seuls_les_chirps_recents_sont_affiches()
    {
        $user = User::factory()->create();

        // Créer des chirps avec différentes dates
        Chirp::factory()->create([
            'user_id' => $user->id,
            'message' => 'Chirp récent',
            'created_at' => Carbon::now()->subDays(3),
        ]);

        Chirp::factory()->create([
            'user_id' => $user->id,
            'message' => 'Chirp ancien',
            'created_at' => Carbon::now()->subDays(10),
        ]);

        $this->actingAs($user);

        $response = $this->get('/chirps');

        $response->assertStatus(200);
        $response->assertSee('Chirp récent');
        $response->assertDontSee('Chirp ancien');
    }

    public function test_un_utilisateur_peut_liker_un_chirp()
    {
        $user = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post("/chirps/{$chirp->id}/like");

        $response->assertStatus(302);
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'chirp_id' => $chirp->id,
        ]);
    }

    public function test_un_utilisateur_ne_peut_pas_liker_deux_fois_le_meme_chirp()
    {
        $user = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        // Liker une première fois
        $this->post("/chirps/{$chirp->id}/like");

        // Tenter de liker une deuxième fois
        $response = $this->post("/chirps/{$chirp->id}/like");

        $response->assertSessionHasErrors(['message' => 'Vous avez déjà liké ce chirp.']);
        $this->assertEquals(1, Like::where('user_id', $user->id)->where('chirp_id', $chirp->id)->count());
    }
}






