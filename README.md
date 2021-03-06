# Portal do Przeprowadzania aukcji Pieców Wędzarniczych - Aplikacja portalu aukcyjnego oparta na frameworku Symfony 5.3
## Wersje live:
Amazon Web Services: 
> http://ec2-3-138-247-210.us-east-2.compute.amazonaws.com

Heroku (na tej platformie nie działa wysyłanie maili i dodawanie zdjęć do aukcji z powodu zablokowania pewnych funkcji PHP)
> https://pdpapw.herokuapp.com/
> 
## Features:
__Aukcje:__
  - Dodawanie aukcji z opcją drag&drop obrazków dla aukcji
  - Filtrowanie aukcji po: nazwie, czy jest zakończona, po cenie, po sprzedającym, sortowanie: wg ilosci ofert, komentarzy, cenie, daty zakonczenia, utworzenia
  - System dodawania aukcji do ulubionych i wyświetlania listy ulubionych aukcji
  - Przeglądanie aukcji: galeria zdjęć aukcji stworzona za pomocą bibliotek FancyGallery i Swiper
  - Timer do zakończenia aukcji
  - Opis aukcji i nazwa aukcji pochodzą z generatora losowych imion i losowego tekstu (mojego)
  - Powiadomienie podczas gdy wygramy aukcję
  - Powiadomienie podczas gdy sprzedamy aukcję
  
  __Licytowanie:__
  - Licytować mogą tylko zalogowani
  - Powiadomienie dla użytkownika o tym, że jego oferta została przebita
  - Właściciel aukcji może ją zakończyć przed czasem, albo usunąć
  
__System komentarzy:__
  - Komentotwać można aukcje i sprzedających
  - Komentarze pod aukcjami można "lajkować" i "dislajkować"
  - Można odpowiadać na komentarze
  - Edycja komentarzy
  - Komentarze nt użytkowników mogą wystawiać tylko kupujący, i określają oni czy typ komentarza to pozytywny negatywny czy neutralny
  - Sprzedający może udzielić odpowiedzi na komentarz kupującego
  - Powiadomienie podczas gdy na nasz komentarz ktoś odpowie
  - Powiadomienie podczas otrzymania komentarza sprzedaży
  - Powiadomienie podczas odpowiedzi na komentarz kupującego
    
  
  
__System powiadomień:__
  - Przy wygraniu aukcji
  - Przy sprzedaniu aukcji
  - Przy odpowiedzi na nasz komentarz
  - Przy otrzymaniu komentarza sprzedaży
  - Przy otrzymaniu odpowiedzi na komentarz sprzedaży
  - Po przeczytaniu powiadomienia są usuwane po godzinie od zobaczenia
    
 __Opcje administratora:__
  - banowanie użytkowników i ręczna weryfikacja konta
  - może usunąć albo zakonćzyć przed czasem każdą aukcję
  - może usunąć albo edytować każdy komentarz
  - panel przywracania usunietych aukcji i komentarzy
    
    
    
W tym oczywiście system użytkowników z typowymi funkcjami typu rejestracja, zmiana hasla, aktywacja konta na mailu. Użytkownicy mają swoje punkty za komentarze. Na publicznej karcie użytkownia informacje takie jak liczba sprzedawanych aukcji, liczb wygranych aukcji i suma wartosci i inne informacje.
